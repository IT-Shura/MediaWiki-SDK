<?php

namespace Tests\MediaWiki;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Mediawiki\Storage\StorageInterface;
use MediaWiki\Bot\CommandManager;
use MediaWiki\Bot\Command;
use MediaWiki\Bot\Project;
use MediaWiki\Api\ApiCollection;
use Tests\Stubs\ProjectExample;
use Tests\Stubs\CommandExample;
use Tests\Stubs\CommandWithoutName;
use org\bovigo\vfs\vfsStream;
use Tests\TestCase;
use Mockery;

class CommandTest extends TestCase
{
    /**
     * @expectedException LogicException
     */
    public function testConstructWithoutName()
    {
        $project = new CommandWithoutName();
    }

    public function testSetGetProject()
    {
        $command = new CommandExample();

        $this->assertNull($command->getProject());

        $project = $this->createProject();

        $command = new CommandExample(null, $project);

        $this->assertEquals($project, $command->getProject());

        $project = $this->createProject();

        $command = new CommandExample();

        $command->setProject($project);

        $this->assertEquals($project, $command->getProject());
    }

    public function testSetGetInput()
    {
        $command = new CommandExample();

        $this->assertNull($command->getInput());

        $input = Mockery::mock(InputInterface::class);

        $command->setInput($input);

        $this->assertEquals($input, $command->getInput());
    }

    public function testSetGetOutput()
    {
        $command = new CommandExample();

        $this->assertNull($command->getOutput());

        $output = Mockery::mock(OutputInterface::class);

        $command->setOutput($output);

        $this->assertEquals($output, $command->getOutput());
    }

    public function testRun()
    {
        $storage = Mockery::mock(StorageInterface::class);

        $project = $this->createProjectMock();
        $commandManager = $this->createCommandManager();

        $input = Mockery::mock(InputInterface::class);
        $output = Mockery::mock(OutputInterface::class);

        $command = Mockery::mock(CommandExample::class.'[handle]');

        $command->shouldReceive('handle')->once()->andReturn('foo');

        $this->assertEquals('foo', $command->execute($input, $output));

        $command = Mockery::mock(Command::class);

        $command->shouldReceive('fire')->once()->andReturn('foo');

        $this->assertEquals('foo', $command->execute($input, $output));
    }

    public function testCall()
    {
        $storage = Mockery::mock(StorageInterface::class);

        $project = $this->createProjectMock();
        $commandManager = $this->createCommandManager();

        $output = Mockery::mock(OutputInterface::class);

        $fooCommand = Mockery::mock(Command::class);

        $fooCommand->shouldReceive('run')->once()->andReturn('bar');

        $commandManager->shouldReceive('getCommand')->with('foo')->once()->andReturn($fooCommand);

        $command = new CommandExample($storage, $project, $commandManager);

        $command->setOutput($output);

        $this->assertEquals('bar', $command->call('foo'));
    }

    public function testCallSilent()
    {
        $storage = Mockery::mock(StorageInterface::class);

        $project = $this->createProjectMock();
        $commandManager = $this->createCommandManager();

        $fooCommand = Mockery::mock(Command::class);

        $fooCommand->shouldReceive('run')->once()->andReturn('bar');

        $commandManager->shouldReceive('getCommand')->with('foo')->once()->andReturn($fooCommand);

        $command = new CommandExample($storage, $project, $commandManager);

        $this->assertEquals('bar', $command->callSilent('foo'));
    }

    public function testArgument()
    {
        $command = new CommandExample();

        $input = Mockery::mock(InputInterface::class)->shouldReceive('getArguments')->once()->andReturn(['foo' => 'bar'])->getMock();
        $input->shouldReceive('getArgument')->once()->with('foo')->andReturn('bar')->getMock();
        $output = Mockery::mock(OutputInterface::class);

        $command->setInput($input);

        $this->assertEquals(['foo' => 'bar'], $command->argument());
        $this->assertEquals('bar', $command->argument('foo'));
    }

    public function testOption()
    {
        $command = new CommandExample();

        $input = Mockery::mock(InputInterface::class)->shouldReceive('getOptions')->once()->andReturn(['foo' => 'bar'])->getMock();
        $input->shouldReceive('getOption')->once()->with('foo')->andReturn('bar')->getMock();

        $command->setInput($input);

        $this->assertEquals(['foo' => 'bar'], $command->option());
        $this->assertEquals('bar', $command->option('foo'));
    }

    public function testConfirm()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('confirm')->once()->with('foo', true)->andReturn('bar')->getMock();

        $command->setOutput($output);

        $this->assertEquals('bar', $command->confirm('foo', true));
    }

    public function testAsk()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('ask')->once()->with('foo', 'bar')->andReturn('foo')->getMock();

        $command->setOutput($output);

        $this->assertEquals('foo', $command->ask('foo', 'bar'));
    }

    public function testAnticipate()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('askQuestion')->once()->withArgs(function ($question) {
            if (!$question instanceof Question) {
                return false;
            }

            if ($question->getQuestion() !== 'foobar') {
                return false;
            }

            if ($question->getDefault() !== 'bar') {
                return false;
            }

            if ($question->getAutocompleterValues() !== ['foo', 'bar', 'baz']) {
                return false;
            }

            return true;
        })->andReturn('baz')->getMock();

        $command->setOutput($output);

        $this->assertEquals('baz', $command->anticipate('foobar', ['foo', 'bar', 'baz'], 'bar'));
    }

    public function testAskWithCompletion()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('askQuestion')->once()->withArgs(function ($question) {
            if (!$question instanceof Question) {
                return false;
            }

            if ($question->getQuestion() !== 'foobar') {
                return false;
            }

            if ($question->getDefault() !== 'bar') {
                return false;
            }

            if ($question->getAutocompleterValues() !== ['foo', 'bar', 'baz']) {
                return false;
            }

            return true;
        })->andReturn('baz')->getMock();

        $command->setOutput($output);

        $this->assertEquals('baz', $command->askWithCompletion('foobar', ['foo', 'bar', 'baz'], 'bar'));
    }

    public function testSecret()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('askQuestion')->once()->withArgs(function ($question) {
            if (!$question instanceof Question) {
                return false;
            }

            if ($question->getQuestion() !== 'foobar') {
                return false;
            }

            if ($question->isHidden() === false) {
                return false;
            }

            if ($question->isHiddenFallback()) {
                return false;
            }

            return true;
        })->andReturn('baz')->getMock();

        $command->setOutput($output);

        $this->assertEquals('baz', $command->secret('foobar', false));
    }

    public function testChoice()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('askQuestion')->once()->withArgs(function ($question) {
            if (!$question instanceof ChoiceQuestion) {
                return false;
            }

            if ($question->getQuestion() !== 'foobar') {
                return false;
            }

            if ($question->getChoices() !== ['foo', 'bar']) {
                return false;
            }

            if ($question->getDefault() !== 'foo') {
                return false;
            }

            if ($question->getMaxAttempts() !== 3) {
                return false;
            }

            return true;
        })->andReturn('baz')->getMock();

        $command->setOutput($output);

        $this->assertEquals('baz', $command->choice('foobar', ['foo', 'bar'], 'foo', 3));
    }

    public function testTable()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class);

        $command->setOutput($output);

        $headers = ['foo', 'bar'];
        $rows = [['foo', 'bar'], ['baz', 'bar']];

        $tableHelper = Mockery::mock('overload:Symfony\Component\Console\Helper\Table');

        $tableHelper->shouldReceive('setHeaders')->once()->with($headers)->andReturn(Mockery::self());
        $tableHelper->shouldReceive('setRows')->with($rows)->andReturn(Mockery::self());
        $tableHelper->shouldReceive('setStyle')->with('default')->andReturn(Mockery::self());
        $tableHelper->shouldReceive('render');

        $command->table($headers, $rows);
    }

    public function testInfo()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('writeln')->once()->with('<info>foo</info>', null);
        $output->shouldReceive('writeln')->once()->with('<info>foo</info>', false);

        $command->setOutput($output);

        $command->info('foo');
        $command->info('foo', false);
    }

    public function testLine()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('writeln')->once()->with('foo', null);
        $output->shouldReceive('writeln')->once()->with('<style>foo</style>', null);
        $output->shouldReceive('writeln')->once()->with('<style>foo</style>', true);
        $output->shouldReceive('writeln')->once()->with('foo', true);

        $command->setOutput($output);

        $command->line('foo');
        $command->line('foo', 'style');
        $command->line('foo', 'style', true);
        $command->line('foo', null, true);
    }

    public function testComment()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('writeln')->once()->with('<comment>foo</comment>', null);
        $output->shouldReceive('writeln')->once()->with('<comment>foo</comment>', false);

        $command->setOutput($output);

        $command->comment('foo');
        $command->comment('foo', false);
    }

    public function testQuestion()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('writeln')->once()->with('<question>foo</question>', null);
        $output->shouldReceive('writeln')->once()->with('<question>foo</question>', false);

        $command->setOutput($output);

        $command->question('foo');
        $command->question('foo', false);
    }

    public function testError()
    {
        $command = new CommandExample();

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('writeln')->once()->with('<error>foo</error>', null);
        $output->shouldReceive('writeln')->once()->with('<error>foo</error>', false);

        $command->setOutput($output);

        $command->error('foo');
        $command->error('foo', false);
    }

    public function testWarning()
    {
        $command = new CommandExample();

        $formatter = Mockery::mock(OutputFormatterInterface::class)->shouldReceive('hasStyle')->twice()->with('warning')->andReturn(false, true)->getMock();

        $formatter->shouldReceive('setStyle')->once()->withArgs(function ($name, $style) {
            if ($name !== 'warning') {
                return false;
            }

            if (!$style instanceof OutputFormatterStyle) {
                return false;
            }

            return true;
        });

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('getFormatter')->times(3)->andReturn($formatter);
        $output->shouldReceive('writeln')->once()->with('<warning>foo</warning>', null);
        $output->shouldReceive('writeln')->once()->with('<warning>foo</warning>', false);

        $command->setOutput($output);

        $command->warning('foo');
        $command->warning('foo', false);
    }

    protected function createProject()
    {
        $apiCollection = new ApiCollection();

        return new ProjectExample($apiCollection);
    }

    protected function createProjectMock()
    {
        $apiCollection = new ApiCollection();

        return Mockery::mock(ProjectExample::class, [$apiCollection]);
    }

    protected function createCommandManager()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $commandsFolder = vfsStream::url('commands');

        return Mockery::mock(CommandManager::class, [$storage, $commandsFolder]);
    }
}
