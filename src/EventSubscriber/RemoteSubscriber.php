<?php

/**
 * This file is part of remote-block
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Block\Remote\EventSubscriber;

use Bldr\Block\Core\Task\AbstractTask;
use Bldr\Block\Execute\Task\ExecuteTask;
use Bldr\Event;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class RemoteSubscriber implements EventSubscriberInterface
{

    /**
     * @type InputInterface
     */
    private $input;

    /**
     * @var OutputInterface $output
     */
    private $output;

    /**
     * @type HelperSet
     */
    private $helpers;

    /**
     * @var string[] $hosts
     */
    private $hosts;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param HelperSet       $helpers
     * @param string[]        $hosts
     */
    public function __construct(InputInterface $input, OutputInterface $output, HelperSet $helpers, array $hosts)
    {
        $this->input   = $input;
        $this->output  = $output;
        $this->helpers = $helpers;
        $this->hosts   = $hosts;
    }

    /**
     * @param Event\PreExecuteEvent $event
     *
     * @throws \Exception
     */
    public function onPreExecute(Event\PreExecuteEvent $event)
    {
        $task = $event->getTask();
        if (!($task instanceof AbstractTask)) {
            return;
        }

        $event->stopPropagation();

        $remote = $task->getParameter('remote');
        if (!isset($this->hosts[$remote])) {
            throw new \Exception(
                sprintf(
                    "The given host (%s) doesn't exist. Must be one of: %s",
                    $remote,
                    implode(array_keys($this->hosts))
                )
            );
        }

        $ssh    = $this->createSSHHandler($this->hosts[$remote]);
        $output = $ssh->exec($event->getProcessBuilder()->getProcess()->getCommandLine());
        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $this->output->writeln(
                [
                    "<comment>------Remote------</comment>",
                    $output,
                    "<comment>-----/Remote------</comment>"
                ]
            );
        }
    }

    /**
     * @param array $config
     *
     * @return \Net_SSH2
     * @throws \Exception
     */
    private function createSSHHandler(array $config)
    {
        $ssh = new \Net_SSH2($config['hostname'], $config['port'], $config['timeout']);

        $password = $this->getPassword($config);
        if (empty($config['rsa_key'])) {
            $login = $ssh->login($config['username'], $password);
            $type  = 'password';
        } else {
            $login = $ssh->login($config['username'], $this->getKey($config, $password));
            $type  = 'key';
        }

        if ($login !== true) {
            throw new \Exception(
                sprintf(
                    "Failed logging in to %s@%s:%d. Using type: %s. \nErrors: %s",
                    $config['username'],
                    $config['hostname'],
                    $config['port'],
                    $type,
                    json_encode($ssh->getErrors(), true)
                )
            );
        }

        return $ssh;
    }

    /**
     * @param array $config
     * @param       $password
     *
     * @return \Crypt_RSA
     */
    private function getKey(array $config, $password)
    {
        $key = rtrim(file_get_contents($config['rsa_key']));
        $rsa = new \Crypt_RSA();
        if (null !== $password) {
            $rsa->setPassword($password);
        }
        $rsa->loadKey($key);

        return $rsa;
    }

    /**
     * @param array $config
     *
     * @return string
     */
    private function getPassword(array $config)
    {
        if (empty($config['password'])) {
            if (empty($config['rsa_key'])) {

                if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                    $this->output->writeln('No password found in config.');
                }

                $question = new Question(
                    sprintf(
                        "What is your password for %s@%s:%d: ",
                        $config['username'],
                        $config['hostname'],
                        $config['port']
                    )
                );
                $question->setHidden(true);
                $question->setHiddenFallback(false);

                /** @type QuestionHelper $q */
                $q = $this->helpers->get('question');

                return $q->ask($this->input, $this->output, $question);
            }

            return '';
        }

        return $config['password'];
    }

    /**
     * @param Event\PreInitializeTaskEvent $event
     */
    public function onPreInitializeTask(Event\PreInitializeTaskEvent $event)
    {
        $task = $event->getTask();
        if ($task instanceof AbstractTask && $task instanceof ExecuteTask) {
            $task->addParameter('remote', false, 'Remote server to run');
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Event::PRE_INITIALIZE_TASK => [
                ['onPreInitializeTask', 0]
            ],
            Event::PRE_EXECUTE => [
                ['onPreExecute', 0]
            ]
        ];
    }
}
