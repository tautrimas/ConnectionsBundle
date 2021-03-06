<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Command;

use ONGR\ConnectionsBundle\Command\SyncTriggersTableUpdateCommand;
use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\TableManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Unit test for UpdateSyncTable command.
 */
class SyncTriggersTableUpdateCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test ongr:sync:triggers:table-update behavior.
     *
     * @param mixed  $result
     * @param string $message
     *
     * @dataProvider getTestCommandData()
     */
    public function testCommand($result, $message)
    {
        $container = new ContainerBuilder();

        $command = new SyncTriggersTableUpdateCommand();
        $command->setContainer($container);

        /** @var InputInterface $input */
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        /** @var OutputInterface|MockObject $output */
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $output->expects($this->once())->method('writeln')->with($this->stringContains($message));

        $tableManager = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\TableManager')
            ->disableOriginalConstructor()
            ->getMock();

        $tableManager->expects($this->once())->method('updateTable')->willReturn($result);

        $container->set('ongr_connections.sync.table_manager', $tableManager);

        $command->run($input, $output);
    }

    /**
     * Data provider for testCommand().
     *
     * @return array[]
     */
    public function getTestCommandData()
    {
        return [
            [true, 'successfully updated'],
            [null, 'up to date'],
            [false, 'Failed to update'],
        ];
    }

    /**
     * Data provider for testCommandCustomConnection().
     *
     * @return array
     */
    public function getCustomConnectionData()
    {
        $out = [
            // Case #0 custom connection is available.
            [true, 'test_connection'],
            // Case #1 custom connection is not available.
            [false, 'test_connection_invalid'],
        ];

        return $out;
    }

    /**
     * Test for command() with custom connection.
     *
     * @param bool        $connectionValid
     * @param string|null $connectionId
     *
     * @dataProvider getCustomConnectionData
     */
    public function testCommandCustomConnection($connectionValid, $connectionId)
    {
        $manager = $this->getTableManagerMock();

        if ($connectionValid) {
            $callCount = $this->once();
        } else {
            $callCount = $this->never();
        }

        $manager->expects($callCount)
            ->method('updateTable')
            ->with($this->isInstanceOf('Doctrine\DBAL\Driver\Connection'));

        $container = new ContainerBuilder();
        $container->set('ongr_connections.sync.table_manager', $manager);

        if ($connectionValid) {
            $connection = $this->getMock('Doctrine\DBAL\Driver\Connection');
            $container->set("doctrine.dbal.{$connectionId}_connection", $connection);
        } else {
            $this->setExpectedException('InvalidArgumentException', 'DBAL connection with ID');
        }

        $command = new SyncTriggersTableUpdateCommand();
        $command->setContainer($container);

        $application = new Application();
        $application->add($command);

        $commandForTesting = $application->find('ongr:sync:triggers:table-update');
        $commandTester = new CommandTester($commandForTesting);

        $commandTester->execute(
            [
                'command' => $commandForTesting->getName(),
                '--connection' => $connectionId,
            ]
        );
    }

    /**
     * Returns table manager mock.
     *
     * @return TableManager|MockObject
     */
    protected function getTableManagerMock()
    {
        $manager = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\TableManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $manager;
    }
}
