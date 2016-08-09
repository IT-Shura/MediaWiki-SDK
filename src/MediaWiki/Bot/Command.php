<?php

namespace MediaWiki\Bot;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Style\SymfonyStyle as OutputStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use MediaWiki\Storage\StorageInterface;
use MediaWiki\Services\ServiceManager;

class Command extends SymfonyCommand
{
    use AuthTrait;

    /**
     * @var MediaWiki\Bot\CommandManager
     */
    protected $commandManager;

    /**
     * @var MediaWiki\Services\ServiceManager
     */
    protected $serviceManager;

    /**
     * @var MediaWiki\Bot\ProjectManager
     */
    protected $projectManager;

    /**
     * @var MediaWiki\Bot\Project
     */
    protected $project;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Constructor.
     * 
     * @param StorageInterface $storage
     * @param Project $project
     * @param CommandManager $commandManager
     */
    public function __construct(StorageInterface $storage = null, Project $project = null, CommandManager $commandManager = null)
    {
        if (!$this->name) {
            throw new LogicException(sprintf('The command defined in "%s" cannot have an empty name', get_class($this)));
        }

        $this->setDescription($this->description);

        $this->storage = $storage;
        $this->project = $project;
        $this->commandManager = $commandManager;

        parent::__construct($this->name);
    }

    /**
     * @param Project $project
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
    }

    /**
     * @param ProjectManager $projectManager
     */
    public function setProjectManager(ProjectManager $projectManager)
    {
        $this->projectManager = $projectManager;
    }

    /**
     * @param ServiceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return ProjectManager
     */
    public function getProjectManager()
    {
        return $this->projectManager;
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager = $serviceManager;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        foreach ($this->getArguments() as $argument) {
            $this->addArgument($argument[0], $argument[1], $argument[2], $argument[3]);
        }

        foreach ($this->getOptions() as $option) {
            $this->addOption($option[0], $option[1], $option[2], $option[3], $option[4]);
        }
    }

    /**
     * Run the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * 
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = new OutputStyle($input, $output);

        return parent::run($input, $output);
    }
    /**
     * Execute the console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * 
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $method = method_exists($this, 'handle') ? 'handle' : 'fire';

        return call_user_func([$this, $method]);
    }

    /**
     * Call another console command.
     *
     * @param string $command
     * @param array $arguments
     * 
     * @return int
     */
    public function call($command, array $arguments = [])
    {
        $instance = $this->commandManager->getCommand($command);

        return $instance->run(new ArrayInput($arguments), $this->output);
    }

    /**
     * Call another console command silently.
     *
     * @param string $command
     * @param array  $arguments
     * 
     * @return int
     */
    public function callSilent($command, array $arguments = [])
    {
        $instance = $this->commandManager->getCommand($command);

        return $instance->run(new ArrayInput($arguments), new NullOutput);
    }

    /**
     * Get the value of a command argument.
     *
     * @param string $key
     * 
     * @return string|array
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get the value of a command option.
     *
     * @param string $key
     * 
     * @return string|array
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Confirm a question with the user.
     *
     * @param string $question
     * @param bool   $default
     * 
     * @return bool
     */
    public function confirm($question, $default = false)
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param string $question
     * @param string $default
     * 
     * @return string
     */
    public function ask($question, $default = null)
    {
        return $this->output->ask($question, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string $question
     * @param array  $choices
     * @param string $default
     * 
     * @return string
     */
    public function anticipate($question, array $choices, $default = null)
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string $question
     * @param array  $choices
     * @param string $default
     * 
     * @return string
     */
    public function askWithCompletion($question, array $choices, $default = null)
    {
        $question = new Question($question, $default);

        $question->setAutocompleterValues($choices);

        return $this->output->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param string $question
     * @param bool   $fallback
     * 
     * @return string
     */
    public function secret($question, $fallback = true)
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param string $question
     * @param array  $choices
     * @param string $default
     * @param mixed  $attempts
     * @param bool   $multiple
     * 
     * @return string
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->output->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param array $headers
     * @param array $rows
     * @param string $style
     */
    public function table(array $headers, $rows, $style = 'default')
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    /**
     * Write a string as information output.
     *
     * @param string|array $string
     * @param null|int|string $verbosity
     */
    public function info($string, $verbosity = null)
    {
        if (is_array($string)) {
            $string = implode(PHP_EOL, $string);
        }

        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as standard output.
     *
     * @param string $string
     * @param string $style
     * @param null|int|string $verbos
     */
    public function line($string, $style = null, $verbosity = null)
    {
        $styled = $style ? "<$style>$string</$style>" : $string;

        $this->output->writeln($styled, $verbosity);
    }

    /**
     * Write a string as comment output.
     *
     * @param string $string
     * @param null|int|string $verbosity
     */
    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as question output.
     *
     * @param string $string
     * @param null|int|string $verbosity
     */
    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param string  $string
     * @param null|int|string $verbosity
     */
    public function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     *
     * @param string  $string
     * @param null|int|string  $verbosity
     */
    public function warn($string, $verbosity = null)
    {
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');

            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosity);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
