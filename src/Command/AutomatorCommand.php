<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Command;

use Contao\Automator;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Runs Contao automator tasks on the command line.
 */
class AutomatorCommand extends AbstractLockedCommand
{
    /**
     * @var array
     */
    private $commands = [];

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('contao:automator')
            ->setDefinition([
                new InputArgument(
                    'task',
                    InputArgument::OPTIONAL,
                    sprintf("The name of the task:\n  - %s", implode("\n  - ", $this->getCommands()))
                ),
            ])
            ->setDescription('Runs automator tasks on the command line.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();

        try {
            $this->runAutomator($input, $output);
        } catch (\InvalidArgumentException $e) {
            $output->writeln(sprintf('%s (see help contao:automator).', $e->getMessage()));

            return 1;
        }

        return 0;
    }

    /**
     * Runs the Automator.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function runAutomator(InputInterface $input, OutputInterface $output): void
    {
        $task = $this->getTaskFromInput($input, $output);

        $automator = new Automator();
        $automator->$task();
    }

    /**
     * Returns a list of available commands.
     *
     * @return array
     */
    private function getCommands(): array
    {
        if (empty($this->commands)) {
            $this->commands = $this->generateCommandMap();
        }

        return $this->commands;
    }

    /**
     * Generates the command map from the Automator class.
     *
     * @return array
     */
    private function generateCommandMap(): array
    {
        $this->framework->initialize();

        $commands = [];

        // Find all public methods
        $class = new \ReflectionClass(Automator::class);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if (!$method->isConstructor() && $method->getDeclaringClass()->getName() === $class->getName()) {
                $commands[] = $method->name;
            }
        }

        return $commands;
    }

    /**
     * Returns the task name from the argument list or via an interactive dialog.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string
     */
    private function getTaskFromInput(InputInterface $input, OutputInterface $output): string
    {
        $commands = $this->getCommands();
        $task = $input->getArgument('task');

        if (null !== $task) {
            if (!\in_array($task, $commands, true)) {
                throw new \InvalidArgumentException(sprintf('Invalid task "%s"', $task)); // no full stop here
            }

            return $task;
        }

        $question = new ChoiceQuestion('Please select a task:', $commands, 0);
        $question->setMaxAttempts(1);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return $helper->ask($input, $output, $question);
    }
}
