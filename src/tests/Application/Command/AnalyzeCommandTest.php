<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Application\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Dotenv;
use Waldhacker\Pseudify\Core\Command\InvalidArgumentException;

class AnalyzeCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $expected = [
            0 => 'wh_user.username (hpagac) -> wh_meta_data.meta_data (...ame";s:6:"hpagac";s:8:"pas...)',
            1 => 'wh_user.password ($argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U) -> wh_meta_data.meta_data (...rd";s:92:"$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U";s:18:"pa...)',
            2 => 'wh_user.first_name (Donato) -> wh_meta_data.meta_data (...ame";s:6:"Donato";s:9:"las...)',
            3 => 'wh_user.last_name (Keeling) -> wh_meta_data.meta_data (...ame";s:7:"Keeling";s:5:"ema...)',
            4 => 'wh_user.last_name (Keeling) -> wh_log.log_data (...astName":"Keeling","ip":"13...)',
            5 => 'wh_user.email (mcclure.ofelia@example.com) -> wh_meta_data.meta_data (...il";s:26:"mcclure.ofelia@example.com";s:4:"cit...)',
            6 => 'wh_user.email (mcclure.ofelia@example.com) -> wh_log.log_data (...,"email":"mcclure.ofelia@example.com","lastNam...)',
            7 => 'wh_user.email (mcclure.ofelia@example.com) -> wh_log.log_message (...another \"mcclure.ofelia@example.com\""})',
            8 => 'wh_user.city (North Elenamouth) -> wh_meta_data.meta_data (...ty";s:16:"North Elenamouth";}s:4:"ke...)',
            9 => 'wh_user.username (georgiana59) -> wh_meta_data.meta_data (...me";s:11:"georgiana59";s:8:"pas...)',
            10 => 'wh_user.username (georgiana59) -> wh_log.log_data (...me";s:11:"georgiana59";s:8:"las...)',
            11 => 'wh_user.username (georgiana59) -> wh_log.log_message (...another \"georgiana59\""})',
            12 => 'wh_user.password ($argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q) -> wh_meta_data.meta_data (...rd";s:92:"$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q";s:18:"pa...)',
            13 => 'wh_user.first_name (Maybell) -> wh_meta_data.meta_data (...ame";s:7:"Maybell";s:9:"las...)',
            14 => 'wh_user.last_name (Anderson) -> wh_meta_data.meta_data (...ame";s:8:"Anderson";s:5:"ema...)',
            15 => 'wh_user.email (cassin.bernadette@example.net) -> wh_meta_data.meta_data (...il";s:29:"cassin.bernadette@example.net";s:4:"cit...)',
            16 => 'wh_user.city (South Wilfordland) -> wh_meta_data.meta_data (...ty";s:17:"South Wilfordland";}s:4:"ke...)',
            17 => 'wh_user.username (howell.damien) -> wh_meta_data.meta_data (...me";s:13:"howell.damien";s:8:"pas...)',
            18 => 'wh_user.password ($argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs) -> wh_meta_data.meta_data (...rd";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"pa...)',
            19 => 'wh_user.first_name (Mckayla) -> wh_meta_data.meta_data (...ame";s:7:"Mckayla";s:9:"las...)',
            20 => 'wh_user.last_name (Stoltenberg) -> wh_meta_data.meta_data (...me";s:11:"Stoltenberg";s:5:"ema...)',
            21 => 'wh_user.email (conn.abigale@example.net) -> wh_meta_data.meta_data (...il";s:24:"conn.abigale@example.net";s:4:"cit...)',
            22 => 'wh_user.city (Dorothyfort) -> wh_meta_data.meta_data (...ty";s:11:"Dorothyfort";}s:4:"ke...)',
            23 => 'wh_user_session.session_data (4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98) -> wh_log.log_data (...i:0;s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";s:4:"use...)',
            24 => 'wh_user_session.session_data (4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98) -> wh_log.ip (4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98)',
            25 => 'wh_user_session.session_data (244.166.32.78) -> wh_meta_data.meta_data (...ip";s:13:"244.166.32.78";}";}s:4:...)',
            26 => 'wh_user_session.session_data (1321:57fc:460b:d4d0:d83f:c200:4b:f1c8) -> wh_meta_data.meta_data (...ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}";}s:4:...)',
            27 => 'wh_user_session.session_data (1321:57fc:460b:d4d0:d83f:c200:4b:f1c8) -> wh_log.log_data (...ng","ip":"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8"})',
            28 => 'wh_user_session.session_data (1321:57fc:460b:d4d0:d83f:c200:4b:f1c8) -> wh_log.ip (1321:57fc:460b:d4d0:d83f:c200:4b:f1c8)',
            29 => 'wh_user_session.session_data (197.110.248.18) -> wh_meta_data.meta_data (...ip";s:14:"197.110.248.18";}";}s:4:...)',
            30 => '__custom__.__custom__ (lafayette64@example.net) -> wh_log.log_data (...il";s:23:"lafayette64@example.net";s:2:"id"...)',
            31 => '__custom__.__custom__ (Homenick) -> wh_log.log_data (...ame";s:8:"Homenick";s:5:"ema...)',
            32 => '__custom__.__custom__ (Homenick) -> wh_log.log_message (...ar text \"Homenick\", anothe...)',
            33 => 'summary',
            34 => '=======',
            35 => '------------------------------ ---------------------------------------------------------------------------------------------- ------------------------',
            36 => '                         data                                                                                           seems to be in',
            37 => '------------------------------ ---------------------------------------------------------------------------------------------- ------------------------',
            38 => '__custom__.__custom__          Homenick                                                                                       wh_log.log_data',
            39 => '__custom__.__custom__          lafayette64@example.net                                                                        wh_log.log_data',
            40 => '__custom__.__custom__          Homenick                                                                                       wh_log.log_message',
            41 => 'wh_user.city                   Dorothyfort                                                                                    wh_meta_data.meta_data',
            42 => 'wh_user.city                   North Elenamouth                                                                               wh_meta_data.meta_data',
            43 => 'wh_user.city                   South Wilfordland                                                                              wh_meta_data.meta_data',
            44 => 'wh_user.email                  mcclure.ofelia@example.com                                                                     wh_log.log_data',
            45 => 'wh_user.email                  mcclure.ofelia@example.com                                                                     wh_log.log_message',
            46 => 'wh_user.email                  cassin.bernadette@example.net                                                                  wh_meta_data.meta_data',
            47 => 'wh_user.email                  conn.abigale@example.net                                                                       wh_meta_data.meta_data',
            48 => 'wh_user.email                  mcclure.ofelia@example.com                                                                     wh_meta_data.meta_data',
            49 => 'wh_user.first_name             Donato                                                                                         wh_meta_data.meta_data',
            50 => 'wh_user.first_name             Maybell                                                                                        wh_meta_data.meta_data',
            51 => 'wh_user.first_name             Mckayla                                                                                        wh_meta_data.meta_data',
            52 => 'wh_user.last_name              Keeling                                                                                        wh_log.log_data',
            53 => 'wh_user.last_name              Anderson                                                                                       wh_meta_data.meta_data',
            54 => 'wh_user.last_name              Keeling                                                                                        wh_meta_data.meta_data',
            55 => 'wh_user.last_name              Stoltenberg                                                                                    wh_meta_data.meta_data',
            56 => 'wh_user.password               $argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U   wh_meta_data.meta_data',
            57 => 'wh_user.password               $argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q   wh_meta_data.meta_data',
            58 => 'wh_user.password               $argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs   wh_meta_data.meta_data',
            59 => 'wh_user.username               georgiana59                                                                                    wh_log.log_data',
            60 => 'wh_user.username               georgiana59                                                                                    wh_log.log_message',
            61 => 'wh_user.username               georgiana59                                                                                    wh_meta_data.meta_data',
            62 => 'wh_user.username               howell.damien                                                                                  wh_meta_data.meta_data',
            63 => 'wh_user.username               hpagac                                                                                         wh_meta_data.meta_data',
            64 => 'wh_user_session.session_data   1321:57fc:460b:d4d0:d83f:c200:4b:f1c8                                                          wh_log.ip',
            65 => 'wh_user_session.session_data   4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98                                                         wh_log.ip',
            66 => 'wh_user_session.session_data   1321:57fc:460b:d4d0:d83f:c200:4b:f1c8                                                          wh_log.log_data',
            67 => 'wh_user_session.session_data   4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98                                                         wh_log.log_data',
            68 => 'wh_user_session.session_data   1321:57fc:460b:d4d0:d83f:c200:4b:f1c8                                                          wh_meta_data.meta_data',
            69 => 'wh_user_session.session_data   197.110.248.18                                                                                 wh_meta_data.meta_data',
            70 => 'wh_user_session.session_data   244.166.32.78                                                                                  wh_meta_data.meta_data',
            71 => '------------------------------ ---------------------------------------------------------------------------------------------- ------------------------',
        ];

        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../../../build/development/userdata/.env');

        $kernel = self::bootKernel(['environment' => 'test']);
        $application = new Application($kernel);

        $command = $application->find('pseudify:analyze');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['profile' => 'test'], ['decorated' => false, 'verbosity' => ConsoleOutput::VERBOSITY_VERBOSE, 'capture_stderr_separately' => true]);

        $output = $commandTester->getDisplay(true);
        $output = array_values(array_map('trim', array_filter(explode("\n", $output))));

        foreach ($output as $index => $data) {
            $this->assertStringContainsString($expected[$index], $data);
        }
    }

    public function testExecuteWithEmptySourceDataCollection()
    {
        $expected = [
            0 => 'summary',
            1 => '=======',
            2 => 'no data found',
        ];

        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../../../build/development/userdata/.env');

        $kernel = self::bootKernel(['environment' => 'test']);
        $application = new Application($kernel);

        $command = $application->find('pseudify:analyze');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['profile' => 'nop'], ['decorated' => false, 'verbosity' => ConsoleOutput::VERBOSITY_VERBOSE, 'capture_stderr_separately' => true]);

        $output = $commandTester->getDisplay(true);
        $output = array_values(array_map('trim', array_filter(explode("\n", $output))));

        foreach ($output as $index => $data) {
            $this->assertStringContainsString($expected[$index], $data);
        }
    }

    public function testExecuteThrowsExceptionOnMissingProfile()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1619890696);

        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../../../build/development/userdata/.env');

        $kernel = self::bootKernel(['environment' => 'test']);
        $application = new Application($kernel);

        $container = self::getContainer();

        $command = $application->find('pseudify:analyze');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['profile' => 'missing']);
    }
}
