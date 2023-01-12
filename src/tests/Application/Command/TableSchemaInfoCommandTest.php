<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Application\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TableSchemaInfoCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $expected = [
            0 => 'wh_log',
            6 => 'foo',
            7 => '613a323a7b693a303b733a33383a223466623a313434373a646566623a396434373a613265303a613336613a313064333a66...',
            8 => 'a:2:{i:0;s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s...',
            9 => '{"message":"foo text \"ronaldo15\", another \"mcclure.ofelia@example.com\""}',
            10 => '4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98',
            12 => 'wh_meta_data',
            18 => '1f8b08000000000000036592dd6ea33010855f65657159116ca0818922f52fca6ea5d52a4bab46bd89066c821b302c769246...',
            19 => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:4;s:8:"username";s:11:"georgiana59";s:8:"password";s:92:"$argon2i$v=...',
            21 => 'wh_user',
            27 => 'howell.damien',
            28 => '$argon2i$v=19$m=8,t=1,p=1$',
            29 => 'argon2id',
            30 => 'nF5;06?nsS/nE',
            31 => 'Mckayla',
            32 => 'Stoltenberg',
            33 => 'cassin.bernadette@example.net',
            34 => 'South Wilfordland',
            36 => 'wh_user_session',
            42 => 'a:1:{s:7:"last_ip";s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";}',
        ];

        $kernel = self::bootKernel(['environment' => 'test']);
        $application = new Application($kernel);

        $command = $application->find('pseudify:debug:table_schema');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay(true);
        $output = array_values(array_map('trim', array_filter(explode("\n", $output))));

        foreach ($output as $index => $data) {
            if (null === ($expected[$index] ?? null)) {
                continue;
            }

            $this->assertStringContainsString($expected[$index], $data);
        }
    }
}
