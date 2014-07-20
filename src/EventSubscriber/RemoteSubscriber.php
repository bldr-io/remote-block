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

use Bldr\Event\PreExecuteEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class RemoteSubscriber implements EventSubscriberInterface
{
    /**
     * @var string[] $hosts
     */
    private $hosts;

    /**
     * @var OutputInterface $output
     */
    private $output;

    /**
     * @param stirng[] $hosts
     */
    public function __construct(OutputInterface $output, array $hosts)
    {
        $this->hosts  = $hosts;
        $this->output = $output;
    }

    /**
     * @param PreExecuteEvent $event
     *
     * @throws \Exception
     */
    public function onPreExecute(PreExecuteEvent $event)
    {
        $event->stopPropagation();

        $remote = $event->getCall()->getOption('remote', false);
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
        if ($this->output->isVeryVerbose()) {
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

                if ($this->output->isVerbose()) {
                    $this->output->writeln('No password found in config.');
                }
                $this->output->writeln(
                    sprintf(
                        "What is your password for %s@%s:%d: ",
                        $config['username'],
                        $config['hostname'],
                        $config['port']
                    )
                );

                return fgets(STDIN);
            }

            return '';
        }

        return $config['password'];
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'bldr.event.execute.before' => [
                ['onPreExecute', 0]
            ]
        ];
    }
}
 