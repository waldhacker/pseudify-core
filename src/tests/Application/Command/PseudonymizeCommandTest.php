<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Application\Command;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Dotenv;
use Waldhacker\Pseudify\Core\Command\InvalidArgumentException;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\MissingTableException;

class PseudonymizeCommandTest extends KernelTestCase
{
    public function testExecuteWithDryRunOptionOutputsDatabaseQueries()
    {
        $expected = [
            [':dcValue1:\'qkunze\'', ':dcValue2:\'karl13\''],
            [':dcValue1:\'UZ5ij-e/\'', ':dcValue2:\'$argon2i$v=19$m=8,t=1,p=1$amo3Z28zNTlwZG84TG1YZg$1Ka95oewxn3xs/jLrTN0R9lhIxtNnQynBFRdE/70cAQ\''],
            [':dcValue1:\'Melody\'', ':dcValue2:\'Jordyn\''],
            [':dcValue1:\'Schmeler\'', ':dcValue2:\'Shields\''],
            [':dcValue1:\'vwilliamson@carter.com\'', ':dcValue2:\'madaline30@example.net\''],
            [':dcValue1:\'Jaydonport\'', ':dcValue2:\'Lake Tanner\''],
            [':dcValue1:\'edmund.douglas\'', ':dcValue2:\'reilly.chase\''],
            [':dcValue1:\'*UyPJ"}6<,h]fZt\'', ':dcValue2:\'$2y$04$O0XKmRw3wl9mni55dSEJXuj3vygjCgdyUviihec.PTiTAu2SS/C6u\''],
            [':dcValue1:\'Andreanne\'', ':dcValue2:\'Keenan\''],
            [':dcValue1:\'Adams\'', ':dcValue2:\'King\''],
            [':dcValue1:\'pbergnaum@heathcote.com\'', ':dcValue2:\'johns.percy@example.com\''],
            [':dcValue1:\'Philipville\'', ':dcValue2:\'Edwardotown\''],
            [':dcValue1:\'isabel.kemmer\'', ':dcValue2:\'hpagac\''],
            [':dcValue1:\'IV#lz.KcLcDi`wmUB)z\'', ':dcValue2:\'$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U\''],
            [':dcValue1:\'Amalia\'', ':dcValue2:\'Donato\''],
            [':dcValue1:\'Ziemann\'', ':dcValue2:\'Keeling\''],
            [':dcValue1:\'torey32@gmail.com\'', ':dcValue2:\'mcclure.ofelia@example.com\''],
            [':dcValue1:\'Port Graciela\'', ':dcValue2:\'North Elenamouth\''],
            [':dcValue1:\'lgibson\'', ':dcValue2:\'georgiana59\''],
            [':dcValue1:\'?7OFtvZ<Ip!{D_\'', ':dcValue2:\'$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q\''],
            [':dcValue1:\'Annetta\'', ':dcValue2:\'Maybell\''],
            [':dcValue1:\'Lehner\'', ':dcValue2:\'Anderson\''],
            [':dcValue1:\'ankunding.verona@thiel.info\'', ':dcValue2:\'cassin.bernadette@example.net\''],
            [':dcValue1:\'Gradyberg\'', ':dcValue2:\'South Wilfordland\''],
            [':dcValue1:\'jamir.wisozk\'', ':dcValue2:\'howell.damien\''],
            [':dcValue1:\'vDj&[Tj}csAstf`G?\'', ':dcValue2:\'$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs\''],
            [':dcValue1:\'Jeanie\'', ':dcValue2:\'Mckayla\''],
            [':dcValue1:\'Durgan\'', ':dcValue2:\'Stoltenberg\''],
            [':dcValue1:\'runolfsson.stevie@renner.com\'', ':dcValue2:\'conn.abigale@example.net\''],
            [':dcValue1:\'Konopelskichester\'', ':dcValue2:\'Dorothyfort\''],
            [':dcValue1:\'a:1:{s:7:"last_ip";s:14:"254.203.39.249";}\'', ':dcValue2:\'a:1:{s:7:"last_ip";s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";}\''],
            [':dcValue1:\'a:1:{s:7:"last_ip";s:14:"88.178.166.218";}\'', ':dcValue2:\'a:1:{s:7:"last_ip";s:13:"107.66.23.195";}\''],
            [':dcValue1:\'a:1:{s:7:"last_ip";s:14:"121.105.97.216";}\'', ':dcValue2:\'a:1:{s:7:"last_ip";s:13:"244.166.32.78";}\''],
            [':dcValue1:\'a:1:{s:7:"last_ip";s:14:"202.180.222.33";}\'', ':dcValue2:\'a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}\''],
            [':dcValue1:\'a:1:{s:7:"last_ip";s:13:"59.203.150.78";}\'', ':dcValue2:\'a:1:{s:7:"last_ip";s:14:"197.110.248.18";}\''],
            [':dcValue1:\'1f8b08000000000000036551c152c23014fc971c3c4692b4145e0fe80cea8c1ef5e6a1be694309b449a72f08e8f0ef26958e30deb26f37bb2f1b0405df0409b0ad3e0a9623cc2396c04cc57203694e3003b623dd5b6c350b50047283ade9f9de90fbdab25f4987447bd757832403f6b9dcdcbcbf6d4e25dd935f7d3c2d06e24258ac91d6853f76faec807dedac34d53f61d7a0b15e1ffcc02860f631cd27d385a5d75bfb300c27c056a6275f8c5b4e813d6bb4660073600d5e93cb5d5fa38d2005a65b344d3ccbe0d39a7aa71b91dee903b65da379e9dac8858e4ae38fe3fb5e9c759d6e686bcab526af7b969fc622652c525e15a9cecd912632ce16157a1c5cc30c41446d76ded2744346b01252703149f93ce3524c43c045868a19e2eff392b11c25b94c122ed48cab2c5c38fd00322ba8c0e4010000\'', ':dcValue2:\'1f8b080000000000000365525d4f023110fc2fcd3d9a4a7bc7570951236234011340455fc8c215aea1d7d66b112e86ff6e7b7211e3db4e77ba33d32db0987d599630b4e525413d60dd80294322453dc19a3dcb3a0ced2c2f14e41c7948628632bde752e21472c115fae118b076af8b34c0ae1f1041b1d18a8ae8b34fba51deef5cb83eb9307d12bdcb347f7a4de96cf03859c6c3d9f8c5dc8f6fa2a7c9c3deb44abe9d1c1a34ff1825afb7b3974ccacddc3c9bddf0f19d8cf2e5dbfe6634b7958f33cd4506365bb8d2f093999376fa8f682408e5f8c1d551d4b0d96bb4ae949d5eaabbeab0c1d05a14d62deac46d8646ab2d9412aa6c0c4938eb12c2d0d469e9b85af262138e9a0cf11c840c35f56fbbd24a61588a0d487ecd0f901bc9b1e29587d016aeac270d74a15d56ae75e1bbc77a33346c86fed94c1c2e7864b9b542ab450aaeb297f821c048e0b64f4e85a9c3d224c1a4d5c231c5ed8e9f7f26110709f2fb1912741220711777086e605f78fef11bb387fddf33020000\''],
            [':dcValue1:\'1f8b08000000000000036551414ec33010fc8b0f1c43ed242dd91c0a5201098e70e31056899bba4decc8ebd21694bf639b165a71b0e4dd999d598f1152f822c8806de481b312a108b500a61a562ac84b821b605b9256632f992fb907d7d82b9bec1499cf0dfba10c48b433b6899419b08fc5faeaed753dd674476ef9fe388fc019b15a21ad2a7718e451016d6bb450cd3fe2d0a1d24eee5d445260fa212f27d3b9a6976b7d1f9b13604b65c955a72da7c09e246a158b02588797e0626b5bd4a1c881c91e5517eec2ebf4aaddca8ee7b7728ffdd0c9a4367dc07c46b57287d3fb9e8d3683ec68a3ea9524272d2bc753902204292e824c8fc991245246570d3a8caabe87c0037776dc520dd1c34b71c1133ec9936296083ef506671e69f0e07f9f97fd0e654512e6f2cc1fbfc9387e0305049908e5010000\'', ':dcValue2:\'1f8b080000000000000365525b4fc23014fe2fcd1ecda4ddc6a5842851319a800920282fe4cc55d6d0b5752dc262f8efb693458c0f4d7a6edfa5a74023fa65684cd1965518f581f67c4c28e219ea739af40ded52b433ac945030e4421c5194ab3d1322cca0e04ca29f1e0dc6ec5599f9b0e7000228374a121e7c0e702f2806dd0b3bc0177a808395c88aa76546e6b78fd3341acd270b7d3f19064fd387bd6e576c3b3db448f1318e9737f3452ec4e6453febdde87185c745faba1f8e5f4cade38c739d83c9d7b6d2ec24e6c49dfd6bd402b8b4ec601b2b7294f45bed2b696697f2ae4eb6287ae7a5b1ebc67187a2f1db162a01b5378a049c5531a6686695b04ca6acdcf85442112b800b7f27ee6ddf949421a47c03825db303145ab050b25a832f735b3548b7aa5436afde55e9aac76633c46f86fcd94ce4075c6498315cc97506b696173b10a0d8f7764e4ab96ecc92380e71bb1d4624ec741dfe1945e429f0ef6788eb19e704773b218e9290443d7fdcc8f11b4093841d36020000\''],
            [':dcValue1:\'1f8b08000000000000036551cb6ac33010fc96ea038c25bf924da12d948640a1d7928bd9c45b5bd49685a4b63169febd926353438eb3b3a3991d212470b69002fba481b30dc23a60014c566c2321dd585801fbb2641476c43c2c80b5b53cd85eb12ba9d1da9fde540172ffd243f1f6e2bef7f73b7d777e2ec7e962ab6cd036a51b344d723475af84ac6e16758b52393ab991897d8aed7e77fcc5f7b49a271fd258572e923d2945ce61406b9f13176c0eec951a4526800c187528dbd914adc42c7ba41376baa5a8377560fc3147e986e9b5adc16a3850a02e73652254266e2ae31e59b256f6aaacf09a27f533041e768b299ad473672216115fc59110224a126fb0f0488207ffffa67414f90b789c7b411189248fb888bde4f2073233dbe4d0010000\'', ':dcValue2:\'1f8b08000000000000036592dd6ea33010855f65657159116ca0818922f52fca6ea5d52a4bab46bd89066c821b302c769246ddbcfbda6ca246eae5f11cfb3be31984103e34444036e240c90421759a01919c4c2444130d0990ad16bdc246102b2905b2166dbf96a8304ec97f47875aefdb9e3b99daeb1ef6eb5631e9eda634f59a697265a6f4aa9b522f7b7e7c142faff36c3edb67ac624fb37dfcbcf636f78b261ac50f7fd4dbb21885995cee65639eee76f9c3aff47bf07b4bcbb8da2c76b3d1624871c15c55a8ab953974e214e6c4e65f8c5d8d5219f16e864a601b9bbffe28fee232e2e79352f6daaccecd8e81fcc4432eea7a680c488d1755fbf4ade2a2d7ad723206221a948395596f61b152f9b9fb3b2e8c1137e21d9bae16be124302fbed853487016d4959bb35d5b7175997366b8dca663a9e67c3dc6cd897d950abb4b09856ad381a744f5ddb1c08d479c7a7c0b27385d04a1a320af1b82c20ba0e72e0110f80276109050b02887228699158ee053a7468fab926d1790b58e0533f4e7c1ad92d381eff01fdaee8ae4c020000\''],
            [':dcValue1:\'1f8b08000000000000036551cb4ec33010fc171f389ad869fad81c0a5201098e70e31056899bba4decc8ebd216947fc70e8d28eac19267673cbb3b4648e19b60026ca74e82e5088b8825305db15c439613cc81ed493983ad62018a406eb1d58e1f34d9af1dfb95744874b0ae1a2433609fabedcdfbdbb62fe99efcfae369391017c26283b429fca953670774b535525757c2ae416dbc3afa81498199c72c4fa64b43afb7e6612826c0d6da912fc629a7c09e151a3d8005b006ff93abbdabd1449001532dea26de65f06975bd578dc8eed411dbae51bcb46de44246a5f6a771bf176b6ca71adae972a3c82bc7f27e0c52c620e5559031395244da9aa2428f83ab088b8388dad9794add8d7b660b2e93948b2ce1b379f0bf6891c616e2efef26e31b210417f370b8ccc21c7dff03ba84c32ce3010000\'', ':dcValue2:\'1f8b08000000000000036552616bc23010fd2fa11fa5336dd51a299bcc3926e8409d3abfc8d5461b4c93ac89d332fcef4b3bcb043f047277efde7b970b109ffc68121074a005463d20dd32f6086209ea31d2ea69121274d4341790516443ec1394ca13e5dc4d206354a03f8c02ad4f324fcab06b091cc8f75278ccf98e70d7c9a2b06122dc501176d63cc9de9789371f8ca6b13f9c4f16ea75d277dea76f27d52ee8617a6e7ad9d738583ecf1729e7fb95fa50c7e1688dc759fc79ea8f57baf271a3b94941a71b53287a3573d54eee808a0313869e4d3d8a18b67acdf6a3d0b307f152259b04ed58aecda69eb843d0787b808243351b411c6eaa18133433921b2a629aefcb548b209a01e3e5ddb36fbb9542b810b33d70fa44cf90294e5d412b0f659999a2661ac85c9ab4d8c9dc562ff566bc7233dedd66b08d34d59a49b149c054f6029b03824b6ce7ea94a98add52e16ec7c5b8e97a41e8e2d00adc68f8a506feff0d41dd14fa166f0f6edbde96edb8fc020ca4e19b36020000\''],
            [':dcValue1:\'1f8b080000000000000365513d4fc33014fc2bc89d58acda493ff2ba0042aa2a18586080213c12d35ab59dc836f403f5bff31c1a11a99bcf77ba3b9f1132f8099003dbaa83600b842261094cd76ca1215b049803fb0aca3bb48a11141991013f94e15b65adf2ec4fd36208bbc6d79da600b67a1999237fa81eab7bfdbeb3cf77d7c78e1a48cb0d864d190f6d673c03867edd38a92f74ad41eda2dac773d65bfdbdac47eb55271c03fbd43ec4b26f3805766bd1684c809a181c9094f2aa9545e7129a00a3b336e92cc9085d6c9c1acb1bb547db1ac51bbf4e1c0d54e978e8dfffd4f878b5f458696528e5d42f28d382f262414128a81074e3ca1a63d72ba73b0491b4b37345dd76fe6425a4e0623ce1c58c4b31a58041469632c4ffafe57d2951483ecfb89419cf49743afd02c1f09eeddd010000\'', ':dcValue2:\'1f8b080000000000000365915f4fc23014c5bf8aa97b240bedc6c64a480c22c428448828c10772dd0a6b5cd7ba967f1abebbedc448c25b4feeb9f7776e2fd0807e6b1a52f4c10e187580264e138a78863a9c061d4ddb146d34ab4a100c591951942b58438a7e6b0ab4dec92a7332b18d1e546b5912ee6dbb38f144b7dd305ddc505dec4de6e3f9fbf374b478dd0b56f6a6b3af6cb798783cca4b33eb3f42b0187c8adb61381835f953a5462acac27e7bbeaac63d73e88b9764e618f88cb9cc41e74b735075b298a213fbc2a70ae0a5617b730afd966d87d9f5fabe3636295af14a9be5d98a7d598291f54e14157056b49407c60a5eae9d6a51c404f0c2bd89ed13695a6c2ae6cb95b5c00ddb835005f353299cc3fe73cacda1a65af3585626bfba2b989d2d3726479de3df2d88bb05b9b805b64a33adb92c971918a88762bb37c5ce1b9fb2725523028a4818fa388afc80f871dbce3f43040e81ffcf1fa2138004894f62bf15fbd8c6381e7f00628e8a6d25020000\''],
            [':dcValue1:\'65794a3163325679546d46745a534936496d786c63324e6f4c6d4e76626e4e3059573530615734694c434a6c6257467062434936496d4675644739755a54417951475634595731776247557562334a6e496977696247467a64453568625755694f694a61615756745957357549697769615841694f6949794d4449754d5467774c6a49794d69347a4d794a39\'', ':dcValue2:\'65794a3163325679546d46745a534936496e4a76626d46735a4738784e534973496d567459576c73496a6f6962574e6a624856795a5335765a6d5673615746415a586868625842735a53356a623230694c434a7359584e30546d46745a534936496b746c5a577870626d63694c434a7063434936496a457a4d6a45364e54646d597a6f304e6a42694f6d51305a4441365a44677a5a6a706a4d6a41774f6a52694f6d5978597a676966513d3d\''],
            [':dcValue1:\'{"message":"foo text \\\\"lesch.constantin\\\\", another \\\\"antone02@example.org\\\\""}\'', ':dcValue2:\'{"message":"foo text \\\\"ronaldo15\\\\", another \\\\"mcclure.ofelia@example.com\\\\""}\''],
            [':dcValue1:\'36d9:e9f:2d74:55c8:6de4:3af6:f8dc:b547\'', ':dcValue2:\'1321:57fc:460b:d4d0:d83f:c200:4b:f1c8\''],
            [':dcValue1:\'65794a3163325679546d46745a534936496e687a59326870626d356c63694973496d567459576c73496a6f6964324670626d38774e55426c654746746347786c4c6d4e7662534973496d78686333524f5957316c496a6f69556e566c5932746c63694973496d6c77496a6f694d544d304c6a49794f5334784d6a4d754d54677a496e303d\'', ':dcValue2:\'65794a3163325679546d46745a534936496e4e3059584a724c6d70315a4751694c434a6c6257467062434936496e4e796233646c514756345957317762475575626d5630496977696247467a64453568625755694f694a4362336c6c63694973496d6c77496a6f694d5455314c6a49784e5334324e7934784f54456966513d3d\''],
            [':dcValue1:\'{"message":"foo text \\\\"xschinner\\\\", another \\\\"waino05@example.com\\\\""}\'', ':dcValue2:\'{"message":"foo text \\\\"stark.judd\\\\", another \\\\"srowe@example.net\\\\""}\''],
            [':dcValue1:\'134.229.123.183\'', ':dcValue2:\'155.215.67.191\''],
            [':dcValue1:\'613a323a7b693a303b733a31333a2239362e31342e3137332e313830223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a31343a2277616c7465722e627269616e6e65223b733a383a226c6173744e616d65223b733a383a22506172697369616e223b733a353a22656d61696c223b733a32373a227363686f77616c7465722e6a616e65406578616d706c652e6f7267223b733a323a226964223b693a31303b733a343a2275736572223b523a333b7d7d\'', ':dcValue2:\'613a323a7b693a303b733a33383a223466623a313434373a646566623a396434373a613265303a613336613a313064333a66643938223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a31323a226672656964612e6d616e7465223b733a383a226c6173744e616d65223b733a353a2254726f6d70223b733a353a22656d61696c223b733a32333a226c61666179657474653634406578616d706c652e6e6574223b733a323a226964223b693a31303b733a343a2275736572223b523a333b7d7d\''],
            [':dcValue1:\'{"message":"bar text \\\\"Parisian\\\\", another \\\\"walter.brianne\\\\""}\'', ':dcValue2:\'{"message":"bar text \\\\"Tromp\\\\", another \\\\"freida.mante\\\\""}\''],
            [':dcValue1:\'1508:1169:e10a:6392:be3c:4d9f:5917:80c1\'', ':dcValue2:\'4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98\''],
            [':dcValue1:\'613a323a7b693a303b733a31333a223231392e352e3137342e313432223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a373a226c676962736f6e223b733a383a226c6173744e616d65223b733a383a22426f7473666f7264223b733a353a22656d61696c223b733a32303a2265717569676c6579406578616d706c652e6e6574223b733a323a226964223b693a323b733a343a2275736572223b523a333b7d7d\'', ':dcValue2:\'613a323a7b693a303b733a31343a223234332e3230322e3234312e3637223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a31313a2267656f726769616e613539223b733a383a226c6173744e616d65223b733a353a22426c6f636b223b733a353a22656d61696c223b733a31393a226e6f6c616e3131406578616d706c652e6e6574223b733a323a226964223b693a323b733a343a2275736572223b523a333b7d7d\''],
            [':dcValue1:\'{"message":"bar text \\\\"Botsford\\\\", another \\\\"lgibson\\\\""}\'', ':dcValue2:\'{"message":"bar text \\\\"Block\\\\", another \\\\"georgiana59\\\\""}\''],
            [':dcValue1:\'13.100.229.173\'', ':dcValue2:\'243.202.241.67\''],
            [':dcValue1:\'613a323a7b693a303b733a31333a2239392e3234342e34342e313034223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a373a22716d696c6c6572223b733a383a226c6173744e616d65223b733a383a2250726f736163636f223b733a353a22656d61696c223b733a31393a226b636f7277696e406578616d706c652e6f7267223b733a323a226964223b693a39313b733a343a2275736572223b523a333b7d7d\'', ':dcValue2:\'613a323a7b693a303b733a31353a223133322e3138382e3234312e313535223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a373a22637972696c3036223b733a383a226c6173744e616d65223b733a383a22486f6d656e69636b223b733a353a22656d61696c223b733a32313a22636c696e746f6e3434406578616d706c652e6e6574223b733a323a226964223b693a39313b733a343a2275736572223b523a333b7d7d\''],
            [':dcValue1:\'{"message":"bar text \\\\"Prosacco\\\\", another \\\\"qmiller\\\\""}\'', ':dcValue2:\'{"message":"bar text \\\\"Homenick\\\\", another \\\\"cyril06\\\\""}\''],
            [':dcValue1:\'171.97.93.97\'', ':dcValue2:\'132.188.241.155\''],
        ];

        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../../../build/development/userdata/.env');

        $kernel = self::bootKernel(['environment' => 'test']);
        $application = new Application($kernel);

        $command = $application->find('pseudify:pseudonymize');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['profile' => 'test', '--dry-run' => true], ['decorated' => false]);

        $output = $commandTester->getDisplay(true);
        $output = array_values(array_map('trim', array_filter(explode("\n", $output))));

        foreach ($output as $index => $sql) {
            foreach ($expected[$index] as $expectedString) {
                $this->assertStringContainsString($expectedString, $sql);
            }
        }
    }

    public function testExecuteReplacesDatabaseValues()
    {
        $expected = [
            'wh_user' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'username' => 'karl13',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$amo3Z28zNTlwZG84TG1YZg$1Ka95oewxn3xs/jLrTN0R9lhIxtNnQynBFRdE/70cAQ',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => '6bJ=yq',
                        'first_name' => 'Jordyn',
                        'last_name' => 'Shields',
                        'email' => 'madaline30@example.net',
                        'city' => 'Lake Tanner',
                    ],
                    'processed' => [
                        'id' => 1,
                        'username' => 'qkunze',
                        'password' => 'UZ5ij-e/',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => '6bJ=yq',
                        'first_name' => 'Melody',
                        'last_name' => 'Schmeler',
                        'email' => 'vwilliamson@carter.com',
                        'city' => 'Jaydonport',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'username' => 'reilly.chase',
                        'password' => '$2y$04$O0XKmRw3wl9mni55dSEJXuj3vygjCgdyUviihec.PTiTAu2SS/C6u',
                        'password_hash_type' => 'bcrypt',
                        'password_plaintext' => 'wHiDoIBY<6Up',
                        'first_name' => 'Keenan',
                        'last_name' => 'King',
                        'email' => 'johns.percy@example.com',
                        'city' => 'Edwardotown',
                    ],
                    'processed' => [
                        'id' => 2,
                        'username' => 'edmund.douglas',
                        'password' => '*UyPJ"}6<,h]fZt',
                        'password_hash_type' => 'bcrypt',
                        'password_plaintext' => 'wHiDoIBY<6Up',
                        'first_name' => 'Andreanne',
                        'last_name' => 'Adams',
                        'email' => 'pbergnaum@heathcote.com',
                        'city' => 'Philipville',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'username' => 'hpagac',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U',
                        'password_hash_type' => 'argon2i',
                        'password_plaintext' => '[dvGd#gI',
                        'first_name' => 'Donato',
                        'last_name' => 'Keeling',
                        'email' => 'mcclure.ofelia@example.com',
                        'city' => 'North Elenamouth',
                    ],
                    'processed' => [
                        'id' => 3,
                        'username' => 'isabel.kemmer',
                        'password' => 'IV#lz.KcLcDi`wmUB)z',
                        'password_hash_type' => 'argon2i',
                        'password_plaintext' => '[dvGd#gI',
                        'first_name' => 'Amalia',
                        'last_name' => 'Ziemann',
                        'email' => 'torey32@gmail.com',
                        'city' => 'Port Graciela',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'username' => 'georgiana59',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => 'uGZIc|aX4d',
                        'first_name' => 'Maybell',
                        'last_name' => 'Anderson',
                        'email' => 'cassin.bernadette@example.net',
                        'city' => 'South Wilfordland',
                    ],
                    'processed' => [
                        'id' => 4,
                        'username' => 'lgibson',
                        'password' => '?7OFtvZ<Ip!{D_',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => 'uGZIc|aX4d',
                        'first_name' => 'Annetta',
                        'last_name' => 'Lehner',
                        'email' => 'ankunding.verona@thiel.info',
                        'city' => 'Gradyberg',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'username' => 'howell.damien',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => 'nF5;06?nsS/nE',
                        'first_name' => 'Mckayla',
                        'last_name' => 'Stoltenberg',
                        'email' => 'conn.abigale@example.net',
                        'city' => 'Dorothyfort',
                    ],
                    'processed' => [
                        'id' => 5,
                        'username' => 'jamir.wisozk',
                        'password' => 'vDj&[Tj}csAstf`G?',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => 'nF5;06?nsS/nE',
                        'first_name' => 'Jeanie',
                        'last_name' => 'Durgan',
                        'email' => 'runolfsson.stevie@renner.com',
                        'city' => 'Konopelskichester',
                    ],
                ],
            ],
            'wh_user_session' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'session_data' => 'a:1:{s:7:"last_ip";s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";}',
                    ],
                    'processed' => [
                        'id' => 1,
                        'session_data' => 'a:1:{s:7:"last_ip";s:14:"254.203.39.249";}',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'session_data' => 'a:1:{s:7:"last_ip";s:13:"107.66.23.195";}',
                    ],
                    'processed' => [
                        'id' => 2,
                        'session_data' => 'a:1:{s:7:"last_ip";s:14:"88.178.166.218";}',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'session_data' => 'a:1:{s:7:"last_ip";s:13:"244.166.32.78";}',
                    ],
                    'processed' => [
                        'id' => 3,
                        'session_data' => 'a:1:{s:7:"last_ip";s:14:"121.105.97.216";}',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'session_data' => 'a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}',
                    ],
                    'processed' => [
                        'id' => 4,
                        'session_data' => 'a:1:{s:7:"last_ip";s:14:"202.180.222.33";}',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'session_data' => 'a:1:{s:7:"last_ip";s:14:"197.110.248.18";}',
                    ],
                    'processed' => [
                        'id' => 5,
                        'session_data' => 'a:1:{s:7:"last_ip";s:13:"59.203.150.78";}',
                    ],
                ],
            ],
            'wh_meta_data' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'meta_data' => '1f8b080000000000000365525d4f023110fc2fcd3d9a4a7bc7570951236234011340455fc8c215aea1d7d66b112e86ff6e7b7211e3db4e77ba33d32db0987d599630b4e525413d60dd80294322453dc19a3dcb3a0ced2c2f14e41c7948628632bde752e21472c115fae118b076af8b34c0ae1f1041b1d18a8ae8b34fba51deef5cb83eb9307d12bdcb347f7a4de96cf03859c6c3d9f8c5dc8f6fa2a7c9c3deb44abe9d1c1a34ff1825afb7b3974ccacddc3c9bddf0f19d8cf2e5dbfe6634b7958f33cd4506365bb8d2f093999376fa8f682408e5f8c1d551d4b0d96bb4ae949d5eaabbeab0c1d05a14d62deac46d8646ab2d9412aa6c0c4938eb12c2d0d469e9b85af262138e9a0cf11c840c35f56fbbd24a61588a0d487ecd0f901bc9b1e29587d016aeac270d74a15d56ae75e1bbc77a33346c86fed94c1c2e7864b9b542ab450aaeb297f821c048e0b64f4e85a9c3d224c1a4d5c231c5ed8e9f7f26110709f2fb1912741220711777086e605f78fef11bb387fddf33020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"139.81.0.139";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"139.81.0.139";}}',
                    ],
                    'processed' => [
                        'id' => 1,
                        'meta_data' => '1f8b08000000000000036551c152c23014fc971c3c4692b4145e0fe80cea8c1ef5e6a1be694309b449a72f08e8f0ef26958e30deb26f37bb2f1b0405df0409b0ad3e0a9623cc2396c04cc57203694e3003b623dd5b6c350b50047283ade9f9de90fbdab25f4987447bd757832403f6b9dcdcbcbf6d4e25dd935f7d3c2d06e24258ac91d6853f76faec807dedac34d53f61d7a0b15e1ffcc02860f631cd27d385a5d75bfb300c27c056a6275f8c5b4e813d6bb4660073600d5e93cb5d5fa38d2005a65b344d3ccbe0d39a7aa71b91dee903b65da379e9dac8858e4ae38fe3fb5e9c759d6e686bcab526af7b969fc622652c525e15a9cecd912632ce16157a1c5cc30c41446d76ded2744346b01252703149f93ce3524c43c045868a19e2eff392b11c25b94c122ed48cab2c5c38fd00322ba8c0e4010000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"139.81.0.139";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:12:"jamir.wisozk";s:8:"password";s:17:"vDj&[Tj}csAstf`G?";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:6:"Jeanie";s:9:"last_name";s:6:"Durgan";s:5:"email";s:20:"miguel15@example.com";s:4:"city";s:17:"Konopelskichester";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"121.105.97.216";}";}s:4:"key3";a:1:{s:4:"key4";s:13:"32.244.138.37";}}',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'meta_data' => '1f8b080000000000000365525b4fc23014fe2fcd1ecda4ddc6a5842851319a800920282fe4cc55d6d0b5752dc262f8efb693458c0f4d7a6edfa5a74023fa65684cd1965518f581f67c4c28e219ea739af40ded52b433ac945030e4421c5194ab3d1322cca0e04ca29f1e0dc6ec5599f9b0e7000228374a121e7c0e702f2806dd0b3bc0177a808395c88aa76546e6b78fd3341acd270b7d3f19064fd387bd6e576c3b3db448f1318e9737f3452ec4e6453febdde87185c745faba1f8e5f4cade38c739d83c9d7b6d2ec24e6c49dfd6bd402b8b4ec601b2b7294f45bed2b696697f2ae4eb6287ae7a5b1ebc67187a2f1db162a01b5378a049c5531a6686695b04ca6acdcf85442112b800b7f27ee6ddf949421a47c03825db303145ab050b25a832f735b3548b7aa5436afde55e9aac76633c46f86fcd94ce4075c6498315cc97506b696173b10a0d8f7764e4ab96ecc92380e71bb1d4624ec741dfe1945e429f0ef6788eb19e704773b218e9290443d7fdcc8f11b4093841d36020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:15:"187.135.239.239";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:15:"187.135.239.239";}}',
                    ],
                    'processed' => [
                        'id' => 2,
                        'meta_data' => '1f8b08000000000000036551414ec33010fc8b0f1c43ed242dd91c0a5201098e70e31056899bba4decc8ebd21694bf639b165a71b0e4dd999d598f1152f822c8806de481b312a108b500a61a562ac84b821b605b9256632f992fb907d7d82b9bec1499cf0dfba10c48b433b6899419b08fc5faeaed753dd674476ef9fe388fc019b15a21ad2a7718e451016d6bb450cd3fe2d0a1d24eee5d445260fa212f27d3b9a6976b7d1f9b13604b65c955a72da7c09e246a158b02588797e0626b5bd4a1c881c91e5517eec2ebf4aaddca8ee7b7728ffdd0c9a4367dc07c46b57287d3fb9e8d3683ec68a3ea9524272d2bc753902204292e824c8fc991245246570d3a8caabe87c0037776dc520dd1c34b71c1133ec9936296083ef506671e69f0e07f9f97fd0e654512e6f2cc1fbfc9387e0305049908e5010000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:15:"187.135.239.239";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:12:"jamir.wisozk";s:8:"password";s:17:"vDj&[Tj}csAstf`G?";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:6:"Jeanie";s:9:"last_name";s:6:"Durgan";s:5:"email";s:20:"miguel15@example.com";s:4:"city";s:17:"Konopelskichester";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"121.105.97.216";}";}s:4:"key3";a:1:{s:4:"key4";s:14:"149.21.154.152";}}',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'meta_data' => '1f8b08000000000000036592dd6ea33010855f65657159116ca0818922f52fca6ea5d52a4bab46bd89066c821b302c769246ddbcfbda6ca246eae5f11cfb3be31984103e34444036e240c90421759a01919c4c2444130d0990ad16bdc246102b2905b2166dbf96a8304ec97f47875aefdb9e3b99daeb1ef6eb5631e9eda634f59a697265a6f4aa9b522f7b7e7c142faff36c3edb67ac624fb37dfcbcf636f78b261ac50f7fd4dbb21885995cee65639eee76f9c3aff47bf07b4bcbb8da2c76b3d1624871c15c55a8ab953974e214e6c4e65f8c5d8d5219f16e864a601b9bbffe28fee232e2e79352f6daaccecd8e81fcc4432eea7a680c488d1755fbf4ade2a2d7ad723206221a948395596f61b152f9b9fb3b2e8c1137e21d9bae16be124302fbed853487016d4959bb35d5b7175997366b8dca663a9e67c3dc6cd897d950abb4b09856ad381a744f5ddb1c08d479c7a7c0b27385d04a1a320af1b82c20ba0e72e0110f80276109050b02887228699158ee053a7468fab926d1790b58e0533f4e7c1ad92d381eff01fdaee8ae4c020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:4;s:8:"username";s:11:"georgiana59";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:10:"uGZIc|aX4d";s:10:"first_name";s:7:"Maybell";s:9:"last_name";s:8:"Anderson";s:5:"email";s:29:"cassin.bernadette@example.net";s:4:"city";s:17:"South Wilfordland";}s:4:"key2";a:2:{s:2:"id";i:4;s:12:"session_data";s:65:"a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}";}s:4:"key3";a:1:{s:4:"key4";s:11:"20.1.58.149";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:4;s:8:"username";s:11:"georgiana59";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:10:"uGZIc|aX4d";s:10:"first_name";s:7:"Maybell";s:9:"last_name";s:8:"Anderson";s:5:"email";s:29:"cassin.bernadette@example.net";s:4:"city";s:17:"South Wilfordland";}s:4:"key2";a:2:{s:2:"id";i:4;s:12:"session_data";s:65:"a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}";}s:4:"key3";a:1:{s:4:"key4";s:11:"20.1.58.149";}}',
                    ],
                    'processed' => [
                        'id' => 3,
                        'meta_data' => '1f8b08000000000000036551cb6ac33010fc96ea038c25bf924da12d948640a1d7928bd9c45b5bd49685a4b63169febd926353438eb3b3a3991d212470b69002fba481b30dc23a60014c566c2321dd585801fbb2641476c43c2c80b5b53cd85eb12ba9d1da9fde540172ffd243f1f6e2bef7f73b7d777e2ec7e962ab6cd036a51b344d723475af84ac6e16758b52393ab991897d8aed7e77fcc5f7b49a271fd258572e923d2945ce61406b9f13176c0eec951a4526800c187528dbd914adc42c7ba41376baa5a8377560fc3147e986e9b5adc16a3850a02e73652254266e2ae31e59b256f6aaacf09a27f533041e768b299ad473672216115fc59110224a126fb0f0488207ffffa67414f90b789c7b411189248fb888bde4f2073233dbe4d0010000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:4;s:8:"username";s:11:"georgiana59";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:10:"uGZIc|aX4d";s:10:"first_name";s:7:"Maybell";s:9:"last_name";s:8:"Anderson";s:5:"email";s:29:"cassin.bernadette@example.net";s:4:"city";s:17:"South Wilfordland";}s:4:"key2";a:2:{s:2:"id";i:4;s:12:"session_data";s:65:"a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}";}s:4:"key3";a:1:{s:4:"key4";s:11:"20.1.58.149";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:4;s:8:"username";s:7:"lgibson";s:8:"password";s:14:"?7OFtvZ<Ip!{D_";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:10:"uGZIc|aX4d";s:10:"first_name";s:7:"Annetta";s:9:"last_name";s:6:"Lehner";s:5:"email";s:18:"asia55@example.org";s:4:"city";s:9:"Gradyberg";}s:4:"key2";a:2:{s:2:"id";i:4;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"202.180.222.33";}";}s:4:"key3";a:1:{s:4:"key4";s:15:"106.227.236.120";}}',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'meta_data' => '1f8b08000000000000036552616bc23010fd2fa11fa5336dd51a299bcc3926e8409d3abfc8d5461b4c93ac89d332fcef4b3bcb043f047277efde7b970b109ffc68121074a005463d20dd32f6086209ea31d2ea69121274d4341790516443ec1394ca13e5dc4d206354a03f8c02ad4f324fcab06b091cc8f75278ccf98e70d7c9a2b06122dc501176d63cc9de9789371f8ca6b13f9c4f16ea75d277dea76f27d52ee8617a6e7ad9d738583ecf1729e7fb95fa50c7e1688dc759fc79ea8f57baf271a3b94941a71b53287a3573d54eee808a0313869e4d3d8a18b67acdf6a3d0b307f152259b04ed58aecda69eb843d0787b808243351b411c6eaa18133433921b2a629aefcb548b209a01e3e5ddb36fbb9542b810b33d70fa44cf90294e5d412b0f659999a2661ac85c9ab4d8c9dc562ff566bc7233dedd66b08d34d59a49b149c054f6029b03824b6ce7ea94a98add52e16ec7c5b8e97a41e8e2d00adc68f8a506feff0d41dd14fa166f0f6edbde96edb8fc020ca4e19b36020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:5;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"197.110.248.18";}";}s:4:"key3";a:1:{s:4:"key4";s:14:"83.243.216.115";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:5;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"197.110.248.18";}";}s:4:"key3";a:1:{s:4:"key4";s:14:"83.243.216.115";}}',
                    ],
                    'processed' => [
                        'id' => 4,
                        'meta_data' => '1f8b08000000000000036551cb4ec33010fc171f389ad869fad81c0a5201098e70e31056899bba4decc8ebd216947fc70e8d28eac19267673cbb3b4648e19b60026ca74e82e5088b8825305db15c439613cc81ed493983ad62018a406eb1d58e1f34d9af1dfb95744874b0ae1a2433609fabedcdfbdbb62fe99efcfae369391017c26283b429fca953670774b535525757c2ae416dbc3afa81498199c72c4fa64b43afb7e6612826c0d6da912fc629a7c09e151a3d8005b006ff93abbdabd1449001532dea26de65f06975bd578dc8eed411dbae51bcb46de44246a5f6a771bf176b6ca71adae972a3c82bc7f27e0c52c620e5559031395244da9aa2428f83ab088b8388dad9794add8d7b660b2e93948b2ce1b379f0bf6891c616e2efef26e31b210417f370b8ccc21c7dff03ba84c32ce3010000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:5;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"197.110.248.18";}";}s:4:"key3";a:1:{s:4:"key4";s:14:"83.243.216.115";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:12:"jamir.wisozk";s:8:"password";s:17:"vDj&[Tj}csAstf`G?";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:6:"Jeanie";s:9:"last_name";s:6:"Durgan";s:5:"email";s:20:"miguel15@example.com";s:4:"city";s:17:"Konopelskichester";}s:4:"key2";a:2:{s:2:"id";i:5;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"59.203.150.78";}";}s:4:"key3";a:1:{s:4:"key4";s:13:"111.181.1.252";}}',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'meta_data' => '1f8b080000000000000365915f4fc23014c5bf8aa97b240bedc6c64a480c22c428448828c10772dd0a6b5cd7ba967f1abebbedc448c25b4feeb9f7776e2fd0807e6b1a52f4c10e187580264e138a78863a9c061d4ddb146d34ab4a100c591951942b58438a7e6b0ab4dec92a7332b18d1e546b5912ee6dbb38f144b7dd305ddc505dec4de6e3f9fbf374b478dd0b56f6a6b3af6cb798783cca4b33eb3f42b0187c8adb61381835f953a5462acac27e7bbeaac63d73e88b9764e618f88cb9cc41e74b735075b298a213fbc2a70ae0a5617b730afd966d87d9f5fabe3636295af14a9be5d98a7d598291f54e14157056b49407c60a5eae9d6a51c404f0c2bd89ed13695a6c2ae6cb95b5c00ddb835005f353299cc3fe73cacda1a65af3585626bfba2b989d2d3726479de3df2d88bb05b9b805b64a33adb92c971918a88762bb37c5ce1b9fb2725523028a4818fa388afc80f871dbce3f43040e81ffcf1fa2138004894f62bf15fbd8c6381e7f00628e8a6d25020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:3;s:8:"username";s:6:"hpagac";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U";s:18:"password_hash_type";s:7:"argon2i";s:18:"password_plaintext";s:8:"[dvGd#gI";s:10:"first_name";s:6:"Donato";s:9:"last_name";s:7:"Keeling";s:5:"email";s:26:"mcclure.ofelia@example.com";s:4:"city";s:16:"North Elenamouth";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"239.27.57.12";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:3;s:8:"username";s:6:"hpagac";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U";s:18:"password_hash_type";s:7:"argon2i";s:18:"password_plaintext";s:8:"[dvGd#gI";s:10:"first_name";s:6:"Donato";s:9:"last_name";s:7:"Keeling";s:5:"email";s:26:"mcclure.ofelia@example.com";s:4:"city";s:16:"North Elenamouth";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"239.27.57.12";}}',
                    ],
                    'processed' => [
                        'id' => 5,
                        'meta_data' => '1f8b080000000000000365513d4fc33014fc2bc89d58acda493ff2ba0042aa2a18586080213c12d35ab59dc836f403f5bff31c1a11a99bcf77ba3b9f1132f8099003dbaa83600b842261094cd76ca1215b049803fb0aca3bb48a11141991013f94e15b65adf2ec4fd36208bbc6d79da600b67a1999237fa81eab7bfdbeb3cf77d7c78e1a48cb0d864d190f6d673c03867edd38a92f74ad41eda2dac773d65bfdbdac47eb55271c03fbd43ec4b26f3805766bd1684c809a181c9094f2aa9545e7129a00a3b336e92cc9085d6c9c1acb1bb547db1ac51bbf4e1c0d54e978e8dfffd4f878b5f458696528e5d42f28d382f262414128a81074e3ca1a63d72ba73b0491b4b37345dd76fe6425a4e0623ce1c58c4b31a58041469632c4ffafe57d2951483ecfb89419cf49743afd02c1f09eeddd010000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:3;s:8:"username";s:6:"hpagac";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U";s:18:"password_hash_type";s:7:"argon2i";s:18:"password_plaintext";s:8:"[dvGd#gI";s:10:"first_name";s:6:"Donato";s:9:"last_name";s:7:"Keeling";s:5:"email";s:26:"mcclure.ofelia@example.com";s:4:"city";s:16:"North Elenamouth";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"239.27.57.12";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:3;s:8:"username";s:13:"isabel.kemmer";s:8:"password";s:19:"IV#lz.KcLcDi`wmUB)z";s:18:"password_hash_type";s:7:"argon2i";s:18:"password_plaintext";s:8:"[dvGd#gI";s:10:"first_name";s:6:"Amalia";s:9:"last_name";s:7:"Ziemann";s:5:"email";s:20:"antone02@example.org";s:4:"city";s:13:"Port Graciela";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"121.105.97.216";}";}s:4:"key3";a:1:{s:4:"key4";s:13:"192.83.223.43";}}',
                    ],
                ],
            ],
            'wh_log' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'log_type' => 'foo',
                        'log_data' => '65794a3163325679546d46745a534936496e4a76626d46735a4738784e534973496d567459576c73496a6f6962574e6a624856795a5335765a6d5673615746415a586868625842735a53356a623230694c434a7359584e30546d46745a534936496b746c5a577870626d63694c434a7063434936496a457a4d6a45364e54646d597a6f304e6a42694f6d51305a4441365a44677a5a6a706a4d6a41774f6a52694f6d5978597a676966513d3d',
                        'log_data_plaintext' => '{"userName":"ronaldo15","email":"mcclure.ofelia@example.com","lastName":"Keeling","ip":"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8"}',
                        'log_message' => '{"message":"foo text \"ronaldo15\", another \"mcclure.ofelia@example.com\""}',
                        'ip' => '1321:57fc:460b:d4d0:d83f:c200:4b:f1c8',
                        '_log_data' => '{"userName":"ronaldo15","email":"mcclure.ofelia@example.com","lastName":"Keeling","ip":"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8"}',
                    ],
                    'processed' => [
                        'id' => 1,
                        'log_type' => 'foo',
                        'log_data' => '65794a3163325679546d46745a534936496d786c63324e6f4c6d4e76626e4e3059573530615734694c434a6c6257467062434936496d4675644739755a54417951475634595731776247557562334a6e496977696247467a64453568625755694f694a61615756745957357549697769615841694f6949794d4449754d5467774c6a49794d69347a4d794a39',
                        'log_data_plaintext' => '{"userName":"ronaldo15","email":"mcclure.ofelia@example.com","lastName":"Keeling","ip":"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8"}',
                        'log_message' => '{"message":"foo text \"lesch.constantin\", another \"antone02@example.org\""}',
                        'ip' => '36d9:e9f:2d74:55c8:6de4:3af6:f8dc:b547',
                        '_log_data' => '{"userName":"lesch.constantin","email":"antone02@example.org","lastName":"Ziemann","ip":"202.180.222.33"}',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'log_type' => 'foo',
                        'log_data' => '65794a3163325679546d46745a534936496e4e3059584a724c6d70315a4751694c434a6c6257467062434936496e4e796233646c514756345957317762475575626d5630496977696247467a64453568625755694f694a4362336c6c63694973496d6c77496a6f694d5455314c6a49784e5334324e7934784f54456966513d3d',
                        'log_data_plaintext' => '{"userName":"stark.judd","email":"srowe@example.net","lastName":"Boyer","ip":"155.215.67.191"}',
                        'log_message' => '{"message":"foo text \"stark.judd\", another \"srowe@example.net\""}',
                        'ip' => '155.215.67.191',
                        '_log_data' => '{"userName":"stark.judd","email":"srowe@example.net","lastName":"Boyer","ip":"155.215.67.191"}',
                    ],
                    'processed' => [
                        'id' => 2,
                        'log_type' => 'foo',
                        'log_data' => '65794a3163325679546d46745a534936496e687a59326870626d356c63694973496d567459576c73496a6f6964324670626d38774e55426c654746746347786c4c6d4e7662534973496d78686333524f5957316c496a6f69556e566c5932746c63694973496d6c77496a6f694d544d304c6a49794f5334784d6a4d754d54677a496e303d',
                        'log_data_plaintext' => '{"userName":"stark.judd","email":"srowe@example.net","lastName":"Boyer","ip":"155.215.67.191"}',
                        'log_message' => '{"message":"foo text \"xschinner\", another \"waino05@example.com\""}',
                        'ip' => '134.229.123.183',
                        '_log_data' => '{"userName":"xschinner","email":"waino05@example.com","lastName":"Ruecker","ip":"134.229.123.183"}',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a33383a223466623a313434373a646566623a396434373a613265303a613336613a313064333a66643938223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a31323a226672656964612e6d616e7465223b733a383a226c6173744e616d65223b733a353a2254726f6d70223b733a353a22656d61696c223b733a32333a226c61666179657474653634406578616d706c652e6e6574223b733a323a226964223b693a31303b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:12:"freida.mante";s:8:"lastName";s:5:"Tromp";s:5:"email";s:23:"lafayette64@example.net";s:2:"id";i:10;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Tromp\", another \"freida.mante\""}',
                        'ip' => '4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98',
                        '_log_data' => 'a:2:{i:0;s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:12:"freida.mante";s:8:"lastName";s:5:"Tromp";s:5:"email";s:23:"lafayette64@example.net";s:2:"id";i:10;s:4:"user";R:3;}}',
                    ],
                    'processed' => [
                        'id' => 3,
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a31333a2239362e31342e3137332e313830223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a31343a2277616c7465722e627269616e6e65223b733a383a226c6173744e616d65223b733a383a22506172697369616e223b733a353a22656d61696c223b733a32373a227363686f77616c7465722e6a616e65406578616d706c652e6f7267223b733a323a226964223b693a31303b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:12:"freida.mante";s:8:"lastName";s:5:"Tromp";s:5:"email";s:23:"lafayette64@example.net";s:2:"id";i:10;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Parisian\", another \"walter.brianne\""}',
                        'ip' => '1508:1169:e10a:6392:be3c:4d9f:5917:80c1',
                        '_log_data' => 'a:2:{i:0;s:13:"96.14.173.180";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:14:"walter.brianne";s:8:"lastName";s:8:"Parisian";s:5:"email";s:27:"schowalter.jane@example.org";s:2:"id";i:10;s:4:"user";R:3;}}',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a31343a223234332e3230322e3234312e3637223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a31313a2267656f726769616e613539223b733a383a226c6173744e616d65223b733a353a22426c6f636b223b733a353a22656d61696c223b733a31393a226e6f6c616e3131406578616d706c652e6e6574223b733a323a226964223b693a323b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:14:"243.202.241.67";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:11:"georgiana59";s:8:"lastName";s:5:"Block";s:5:"email";s:19:"nolan11@example.net";s:2:"id";i:2;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Block\", another \"georgiana59\""}',
                        'ip' => '243.202.241.67',
                        '_log_data' => 'a:2:{i:0;s:14:"243.202.241.67";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:11:"georgiana59";s:8:"lastName";s:5:"Block";s:5:"email";s:19:"nolan11@example.net";s:2:"id";i:2;s:4:"user";R:3;}}',
                    ],
                    'processed' => [
                        'id' => 4,
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a31333a223231392e352e3137342e313432223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a373a226c676962736f6e223b733a383a226c6173744e616d65223b733a383a22426f7473666f7264223b733a353a22656d61696c223b733a32303a2265717569676c6579406578616d706c652e6e6574223b733a323a226964223b693a323b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:14:"243.202.241.67";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:11:"georgiana59";s:8:"lastName";s:5:"Block";s:5:"email";s:19:"nolan11@example.net";s:2:"id";i:2;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Botsford\", another \"lgibson\""}',
                        'ip' => '13.100.229.173',
                        '_log_data' => 'a:2:{i:0;s:13:"219.5.174.142";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:7:"lgibson";s:8:"lastName";s:8:"Botsford";s:5:"email";s:20:"equigley@example.net";s:2:"id";i:2;s:4:"user";R:3;}}',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a31353a223133322e3138382e3234312e313535223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a373a22637972696c3036223b733a383a226c6173744e616d65223b733a383a22486f6d656e69636b223b733a353a22656d61696c223b733a32313a22636c696e746f6e3434406578616d706c652e6e6574223b733a323a226964223b693a39313b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:15:"132.188.241.155";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:7:"cyril06";s:8:"lastName";s:8:"Homenick";s:5:"email";s:21:"clinton44@example.net";s:2:"id";i:91;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Homenick\", another \"cyril06\""}',
                        'ip' => '132.188.241.155',
                        '_log_data' => 'a:2:{i:0;s:15:"132.188.241.155";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:7:"cyril06";s:8:"lastName";s:8:"Homenick";s:5:"email";s:21:"clinton44@example.net";s:2:"id";i:91;s:4:"user";R:3;}}',
                    ],
                    'processed' => [
                        'id' => 5,
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a31333a2239392e3234342e34342e313034223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a373a22716d696c6c6572223b733a383a226c6173744e616d65223b733a383a2250726f736163636f223b733a353a22656d61696c223b733a31393a226b636f7277696e406578616d706c652e6f7267223b733a323a226964223b693a39313b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:15:"132.188.241.155";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:7:"cyril06";s:8:"lastName";s:8:"Homenick";s:5:"email";s:21:"clinton44@example.net";s:2:"id";i:91;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Prosacco\", another \"qmiller\""}',
                        'ip' => '171.97.93.97',
                        '_log_data' => 'a:2:{i:0;s:13:"99.244.44.104";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:7:"qmiller";s:8:"lastName";s:8:"Prosacco";s:5:"email";s:19:"kcorwin@example.org";s:2:"id";i:91;s:4:"user";R:3;}}',
                    ],
                ],
                6 => [
                    'original' => [
                        'id' => '6',
                        'log_type' => '',
                        'log_data' => '',
                        'log_data_plaintext' => '',
                        'log_message' => '',
                        'ip' => '',
                    ],
                    'processed' => [
                        'id' => '6',
                        'log_type' => '',
                        'log_data' => '',
                        'log_data_plaintext' => '',
                        'log_message' => '',
                        'ip' => '',
                    ],
                ],
            ],
        ];

        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../../../build/development/userdata/.env');

        $kernel = self::bootKernel(['environment' => 'test']);
        $application = new Application($kernel);

        $container = self::getContainer();
        $connection = $container->get(Connection::class);

        foreach ($expected as $table => $expectedRows) {
            $result = $connection->createQueryBuilder()->select('*')->from($connection->quoteIdentifier($table))->orderBy('id', 'ASC')->executeQuery();
            while ($row = $result->fetchAssociative()) {
                foreach ($row as $column => $value) {
                    if (!is_resource($value)) {
                        continue;
                    }
                    $value = stream_get_contents($value);
                    $row[$column] = $value;
                }

                switch ($table) {
                    case 'wh_meta_data':
                        $metaData = gzdecode(hex2bin($row['meta_data']));
                        $row['_meta_data'] = $metaData;
                        break;
                    case 'wh_log':
                        if ('foo' === $row['log_type']) {
                            $metaData = base64_decode(hex2bin($row['log_data']));
                            $row['_log_data'] = $metaData;
                        } elseif ('bar' === $row['log_type']) {
                            $metaData = hex2bin($row['log_data']);
                            $row['_log_data'] = $metaData;
                        }
                        break;
                }

                $this->assertEquals($expectedRows[$row['id']]['original'], $row);
            }
        }

        $command = $application->find('pseudify:pseudonymize');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['profile' => 'test'], ['decorated' => false, 'verbosity' => ConsoleOutput::VERBOSITY_VERBOSE]);

        foreach ($expected as $table => $expectedRows) {
            $result = $connection->createQueryBuilder()->select('*')->from($connection->quoteIdentifier($table))->orderBy('id', 'ASC')->executeQuery();
            while ($row = $result->fetchAssociative()) {
                foreach ($row as $column => $value) {
                    if (!is_resource($value)) {
                        continue;
                    }
                    $value = stream_get_contents($value);
                    $row[$column] = $value;
                }

                switch ($table) {
                    case 'wh_meta_data':
                        $metaData = gzdecode(hex2bin($row['meta_data']));
                        $row['_meta_data'] = $metaData;
                        break;
                    case 'wh_log':
                        if ('foo' === $row['log_type']) {
                            $metaData = base64_decode(hex2bin($row['log_data']));
                            $row['_log_data'] = $metaData;
                        } elseif ('bar' === $row['log_type']) {
                            $metaData = hex2bin($row['log_data']);
                            $row['_log_data'] = $metaData;
                        }
                        break;
                }

                $this->assertEquals($expectedRows[$row['id']]['processed'], $row);
            }
        }
    }

    public function testExecuteDoNotReplacesDatabaseValues()
    {
        $expected = [
            'wh_user' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'username' => 'karl13',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$amo3Z28zNTlwZG84TG1YZg$1Ka95oewxn3xs/jLrTN0R9lhIxtNnQynBFRdE/70cAQ',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => '6bJ=yq',
                        'first_name' => 'Jordyn',
                        'last_name' => 'Shields',
                        'email' => 'madaline30@example.net',
                        'city' => 'Lake Tanner',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'username' => 'reilly.chase',
                        'password' => '$2y$04$O0XKmRw3wl9mni55dSEJXuj3vygjCgdyUviihec.PTiTAu2SS/C6u',
                        'password_hash_type' => 'bcrypt',
                        'password_plaintext' => 'wHiDoIBY<6Up',
                        'first_name' => 'Keenan',
                        'last_name' => 'King',
                        'email' => 'johns.percy@example.com',
                        'city' => 'Edwardotown',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'username' => 'hpagac',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U',
                        'password_hash_type' => 'argon2i',
                        'password_plaintext' => '[dvGd#gI',
                        'first_name' => 'Donato',
                        'last_name' => 'Keeling',
                        'email' => 'mcclure.ofelia@example.com',
                        'city' => 'North Elenamouth',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'username' => 'georgiana59',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => 'uGZIc|aX4d',
                        'first_name' => 'Maybell',
                        'last_name' => 'Anderson',
                        'email' => 'cassin.bernadette@example.net',
                        'city' => 'South Wilfordland',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'username' => 'howell.damien',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => 'nF5;06?nsS/nE',
                        'first_name' => 'Mckayla',
                        'last_name' => 'Stoltenberg',
                        'email' => 'conn.abigale@example.net',
                        'city' => 'Dorothyfort',
                    ],
                ],
            ],
            'wh_user_session' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'session_data' => 'a:1:{s:7:"last_ip";s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";}',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'session_data' => 'a:1:{s:7:"last_ip";s:13:"107.66.23.195";}',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'session_data' => 'a:1:{s:7:"last_ip";s:13:"244.166.32.78";}',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'session_data' => 'a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'session_data' => 'a:1:{s:7:"last_ip";s:14:"197.110.248.18";}',
                    ],
                ],
            ],
            'wh_meta_data' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'meta_data' => '1f8b080000000000000365525d4f023110fc2fcd3d9a4a7bc7570951236234011340455fc8c215aea1d7d66b112e86ff6e7b7211e3db4e77ba33d32db0987d599630b4e525413d60dd80294322453dc19a3dcb3a0ced2c2f14e41c7948628632bde752e21472c115fae118b076af8b34c0ae1f1041b1d18a8ae8b34fba51deef5cb83eb9307d12bdcb347f7a4de96cf03859c6c3d9f8c5dc8f6fa2a7c9c3deb44abe9d1c1a34ff1825afb7b3974ccacddc3c9bddf0f19d8cf2e5dbfe6634b7958f33cd4506365bb8d2f093999376fa8f682408e5f8c1d551d4b0d96bb4ae949d5eaabbeab0c1d05a14d62deac46d8646ab2d9412aa6c0c4938eb12c2d0d469e9b85af262138e9a0cf11c840c35f56fbbd24a61588a0d487ecd0f901bc9b1e29587d016aeac270d74a15d56ae75e1bbc77a33346c86fed94c1c2e7864b9b542ab450aaeb297f821c048e0b64f4e85a9c3d224c1a4d5c231c5ed8e9f7f26110709f2fb1912741220711777086e605f78fef11bb387fddf33020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"139.81.0.139";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"139.81.0.139";}}',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'meta_data' => '1f8b080000000000000365525b4fc23014fe2fcd1ecda4ddc6a5842851319a800920282fe4cc55d6d0b5752dc262f8efb693458c0f4d7a6edfa5a74023fa65684cd1965518f581f67c4c28e219ea739af40ded52b433ac945030e4421c5194ab3d1322cca0e04ca29f1e0dc6ec5599f9b0e7000228374a121e7c0e702f2806dd0b3bc0177a808395c88aa76546e6b78fd3341acd270b7d3f19064fd387bd6e576c3b3db448f1318e9737f3452ec4e6453febdde87185c745faba1f8e5f4cade38c739d83c9d7b6d2ec24e6c49dfd6bd402b8b4ec601b2b7294f45bed2b696697f2ae4eb6287ae7a5b1ebc67187a2f1db162a01b5378a049c5531a6686695b04ca6acdcf85442112b800b7f27ee6ddf949421a47c03825db303145ab050b25a832f735b3548b7aa5436afde55e9aac76633c46f86fcd94ce4075c6498315cc97506b696173b10a0d8f7764e4ab96ecc92380e71bb1d4624ec741dfe1945e429f0ef6788eb19e704773b218e9290443d7fdcc8f11b4093841d36020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:15:"187.135.239.239";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:15:"187.135.239.239";}}',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'meta_data' => '1f8b08000000000000036592dd6ea33010855f65657159116ca0818922f52fca6ea5d52a4bab46bd89066c821b302c769246ddbcfbda6ca246eae5f11cfb3be31984103e34444036e240c90421759a01919c4c2444130d0990ad16bdc246102b2905b2166dbf96a8304ec97f47875aefdb9e3b99daeb1ef6eb5631e9eda634f59a697265a6f4aa9b522f7b7e7c142faff36c3edb67ac624fb37dfcbcf636f78b261ac50f7fd4dbb21885995cee65639eee76f9c3aff47bf07b4bcbb8da2c76b3d1624871c15c55a8ab953974e214e6c4e65f8c5d8d5219f16e864a601b9bbffe28fee232e2e79352f6daaccecd8e81fcc4432eea7a680c488d1755fbf4ade2a2d7ad723206221a948395596f61b152f9b9fb3b2e8c1137e21d9bae16be124302fbed853487016d4959bb35d5b7175997366b8dca663a9e67c3dc6cd897d950abb4b09856ad381a744f5ddb1c08d479c7a7c0b27385d04a1a320af1b82c20ba0e72e0110f80276109050b02887228699158ee053a7468fab926d1790b58e0533f4e7c1ad92d381eff01fdaee8ae4c020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:4;s:8:"username";s:11:"georgiana59";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:10:"uGZIc|aX4d";s:10:"first_name";s:7:"Maybell";s:9:"last_name";s:8:"Anderson";s:5:"email";s:29:"cassin.bernadette@example.net";s:4:"city";s:17:"South Wilfordland";}s:4:"key2";a:2:{s:2:"id";i:4;s:12:"session_data";s:65:"a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}";}s:4:"key3";a:1:{s:4:"key4";s:11:"20.1.58.149";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:4;s:8:"username";s:11:"georgiana59";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:10:"uGZIc|aX4d";s:10:"first_name";s:7:"Maybell";s:9:"last_name";s:8:"Anderson";s:5:"email";s:29:"cassin.bernadette@example.net";s:4:"city";s:17:"South Wilfordland";}s:4:"key2";a:2:{s:2:"id";i:4;s:12:"session_data";s:65:"a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}";}s:4:"key3";a:1:{s:4:"key4";s:11:"20.1.58.149";}}',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'meta_data' => '1f8b08000000000000036552616bc23010fd2fa11fa5336dd51a299bcc3926e8409d3abfc8d5461b4c93ac89d332fcef4b3bcb043f047277efde7b970b109ffc68121074a005463d20dd32f6086209ea31d2ea69121274d4341790516443ec1394ca13e5dc4d206354a03f8c02ad4f324fcab06b091cc8f75278ccf98e70d7c9a2b06122dc501176d63cc9de9789371f8ca6b13f9c4f16ea75d277dea76f27d52ee8617a6e7ad9d738583ecf1729e7fb95fa50c7e1688dc759fc79ea8f57baf271a3b94941a71b53287a3573d54eee808a0313869e4d3d8a18b67acdf6a3d0b307f152259b04ed58aecda69eb843d0787b808243351b411c6eaa18133433921b2a629aefcb548b209a01e3e5ddb36fbb9542b810b33d70fa44cf90294e5d412b0f659999a2661ac85c9ab4d8c9dc562ff566bc7233dedd66b08d34d59a49b149c054f6029b03824b6ce7ea94a98add52e16ec7c5b8e97a41e8e2d00adc68f8a506feff0d41dd14fa166f0f6edbde96edb8fc020ca4e19b36020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:5;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"197.110.248.18";}";}s:4:"key3";a:1:{s:4:"key4";s:14:"83.243.216.115";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:5;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"197.110.248.18";}";}s:4:"key3";a:1:{s:4:"key4";s:14:"83.243.216.115";}}',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'meta_data' => '1f8b080000000000000365915f4fc23014c5bf8aa97b240bedc6c64a480c22c428448828c10772dd0a6b5cd7ba967f1abebbedc448c25b4feeb9f7776e2fd0807e6b1a52f4c10e187580264e138a78863a9c061d4ddb146d34ab4a100c591951942b58438a7e6b0ab4dec92a7332b18d1e546b5912ee6dbb38f144b7dd305ddc505dec4de6e3f9fbf374b478dd0b56f6a6b3af6cb798783cca4b33eb3f42b0187c8adb61381835f953a5462acac27e7bbeaac63d73e88b9764e618f88cb9cc41e74b735075b298a213fbc2a70ae0a5617b730afd966d87d9f5fabe3636295af14a9be5d98a7d598291f54e14157056b49407c60a5eae9d6a51c404f0c2bd89ed13695a6c2ae6cb95b5c00ddb835005f353299cc3fe73cacda1a65af3585626bfba2b989d2d3726479de3df2d88bb05b9b805b64a33adb92c971918a88762bb37c5ce1b9fb2725523028a4818fa388afc80f871dbce3f43040e81ffcf1fa2138004894f62bf15fbd8c6381e7f00628e8a6d25020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:3;s:8:"username";s:6:"hpagac";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U";s:18:"password_hash_type";s:7:"argon2i";s:18:"password_plaintext";s:8:"[dvGd#gI";s:10:"first_name";s:6:"Donato";s:9:"last_name";s:7:"Keeling";s:5:"email";s:26:"mcclure.ofelia@example.com";s:4:"city";s:16:"North Elenamouth";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"239.27.57.12";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:3;s:8:"username";s:6:"hpagac";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U";s:18:"password_hash_type";s:7:"argon2i";s:18:"password_plaintext";s:8:"[dvGd#gI";s:10:"first_name";s:6:"Donato";s:9:"last_name";s:7:"Keeling";s:5:"email";s:26:"mcclure.ofelia@example.com";s:4:"city";s:16:"North Elenamouth";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"239.27.57.12";}}',
                    ],
                ],
            ],
            'wh_log' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'log_type' => 'foo',
                        'log_data' => '65794a3163325679546d46745a534936496e4a76626d46735a4738784e534973496d567459576c73496a6f6962574e6a624856795a5335765a6d5673615746415a586868625842735a53356a623230694c434a7359584e30546d46745a534936496b746c5a577870626d63694c434a7063434936496a457a4d6a45364e54646d597a6f304e6a42694f6d51305a4441365a44677a5a6a706a4d6a41774f6a52694f6d5978597a676966513d3d',
                        'log_data_plaintext' => '{"userName":"ronaldo15","email":"mcclure.ofelia@example.com","lastName":"Keeling","ip":"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8"}',
                        'log_message' => '{"message":"foo text \"ronaldo15\", another \"mcclure.ofelia@example.com\""}',
                        'ip' => '1321:57fc:460b:d4d0:d83f:c200:4b:f1c8',
                        '_log_data' => '{"userName":"ronaldo15","email":"mcclure.ofelia@example.com","lastName":"Keeling","ip":"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8"}',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'log_type' => 'foo',
                        'log_data' => '65794a3163325679546d46745a534936496e4e3059584a724c6d70315a4751694c434a6c6257467062434936496e4e796233646c514756345957317762475575626d5630496977696247467a64453568625755694f694a4362336c6c63694973496d6c77496a6f694d5455314c6a49784e5334324e7934784f54456966513d3d',
                        'log_data_plaintext' => '{"userName":"stark.judd","email":"srowe@example.net","lastName":"Boyer","ip":"155.215.67.191"}',
                        'log_message' => '{"message":"foo text \"stark.judd\", another \"srowe@example.net\""}',
                        'ip' => '155.215.67.191',
                        '_log_data' => '{"userName":"stark.judd","email":"srowe@example.net","lastName":"Boyer","ip":"155.215.67.191"}',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a33383a223466623a313434373a646566623a396434373a613265303a613336613a313064333a66643938223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a31323a226672656964612e6d616e7465223b733a383a226c6173744e616d65223b733a353a2254726f6d70223b733a353a22656d61696c223b733a32333a226c61666179657474653634406578616d706c652e6e6574223b733a323a226964223b693a31303b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:12:"freida.mante";s:8:"lastName";s:5:"Tromp";s:5:"email";s:23:"lafayette64@example.net";s:2:"id";i:10;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Tromp\", another \"freida.mante\""}',
                        'ip' => '4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98',
                        '_log_data' => 'a:2:{i:0;s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:12:"freida.mante";s:8:"lastName";s:5:"Tromp";s:5:"email";s:23:"lafayette64@example.net";s:2:"id";i:10;s:4:"user";R:3;}}',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a31343a223234332e3230322e3234312e3637223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a31313a2267656f726769616e613539223b733a383a226c6173744e616d65223b733a353a22426c6f636b223b733a353a22656d61696c223b733a31393a226e6f6c616e3131406578616d706c652e6e6574223b733a323a226964223b693a323b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:14:"243.202.241.67";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:11:"georgiana59";s:8:"lastName";s:5:"Block";s:5:"email";s:19:"nolan11@example.net";s:2:"id";i:2;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Block\", another \"georgiana59\""}',
                        'ip' => '243.202.241.67',
                        '_log_data' => 'a:2:{i:0;s:14:"243.202.241.67";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:11:"georgiana59";s:8:"lastName";s:5:"Block";s:5:"email";s:19:"nolan11@example.net";s:2:"id";i:2;s:4:"user";R:3;}}',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a31353a223133322e3138382e3234312e313535223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a373a22637972696c3036223b733a383a226c6173744e616d65223b733a383a22486f6d656e69636b223b733a353a22656d61696c223b733a32313a22636c696e746f6e3434406578616d706c652e6e6574223b733a323a226964223b693a39313b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:15:"132.188.241.155";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:7:"cyril06";s:8:"lastName";s:8:"Homenick";s:5:"email";s:21:"clinton44@example.net";s:2:"id";i:91;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Homenick\", another \"cyril06\""}',
                        'ip' => '132.188.241.155',
                        '_log_data' => 'a:2:{i:0;s:15:"132.188.241.155";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:7:"cyril06";s:8:"lastName";s:8:"Homenick";s:5:"email";s:21:"clinton44@example.net";s:2:"id";i:91;s:4:"user";R:3;}}',
                    ],
                ],
                6 => [
                    'original' => [
                        'id' => '6',
                        'log_type' => '',
                        'log_data' => '',
                        'log_data_plaintext' => '',
                        'log_message' => '',
                        'ip' => '',
                    ],
                ],
            ],
        ];

        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../../../build/development/userdata/.env');

        $kernel = self::bootKernel(['environment' => 'test']);
        $application = new Application($kernel);

        $container = self::getContainer();
        $connection = $container->get(Connection::class);

        foreach ($expected as $table => $expectedRows) {
            $result = $connection->createQueryBuilder()->select('*')->from($connection->quoteIdentifier($table))->orderBy('id', 'ASC')->executeQuery();
            while ($row = $result->fetchAssociative()) {
                foreach ($row as $column => $value) {
                    if (!is_resource($value)) {
                        continue;
                    }
                    $value = stream_get_contents($value);
                    $row[$column] = $value;
                }

                switch ($table) {
                    case 'wh_meta_data':
                        $metaData = gzdecode(hex2bin($row['meta_data']));
                        $row['_meta_data'] = $metaData;
                        break;
                    case 'wh_log':
                        if ('foo' === $row['log_type']) {
                            $metaData = base64_decode(hex2bin($row['log_data']));
                            $row['_log_data'] = $metaData;
                        } elseif ('bar' === $row['log_type']) {
                            $metaData = hex2bin($row['log_data']);
                            $row['_log_data'] = $metaData;
                        }
                        break;
                }

                $this->assertEquals($expectedRows[$row['id']]['original'], $row);
            }
        }

        $command = $application->find('pseudify:pseudonymize');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['profile' => 'nop'], ['decorated' => false, 'verbosity' => ConsoleOutput::VERBOSITY_VERBOSE]);

        foreach ($expected as $table => $expectedRows) {
            $result = $connection->createQueryBuilder()->select('*')->from($connection->quoteIdentifier($table))->orderBy('id', 'ASC')->executeQuery();
            while ($row = $result->fetchAssociative()) {
                foreach ($row as $column => $value) {
                    if (!is_resource($value)) {
                        continue;
                    }
                    $value = stream_get_contents($value);
                    $row[$column] = $value;
                }

                switch ($table) {
                    case 'wh_meta_data':
                        $metaData = gzdecode(hex2bin($row['meta_data']));
                        $row['_meta_data'] = $metaData;
                        break;
                    case 'wh_log':
                        if ('foo' === $row['log_type']) {
                            $metaData = base64_decode(hex2bin($row['log_data']));
                            $row['_log_data'] = $metaData;
                        } elseif ('bar' === $row['log_type']) {
                            $metaData = hex2bin($row['log_data']);
                            $row['_log_data'] = $metaData;
                        }
                        break;
                }

                $this->assertEquals($expectedRows[$row['id']]['original'], $row);
            }
        }
    }

    public function testExecuteDoNotUpdatesDatabaseValues()
    {
        $this->markTestSkipped('must be revisited.');

        $expectedDatabaseRows = [
            'wh_user' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'username' => 'karl13',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$amo3Z28zNTlwZG84TG1YZg$1Ka95oewxn3xs/jLrTN0R9lhIxtNnQynBFRdE/70cAQ',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => '6bJ=yq',
                        'first_name' => 'Jordyn',
                        'last_name' => 'Shields',
                        'email' => 'madaline30@example.net',
                        'city' => 'Lake Tanner',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'username' => 'reilly.chase',
                        'password' => '$2y$04$O0XKmRw3wl9mni55dSEJXuj3vygjCgdyUviihec.PTiTAu2SS/C6u',
                        'password_hash_type' => 'bcrypt',
                        'password_plaintext' => 'wHiDoIBY<6Up',
                        'first_name' => 'Keenan',
                        'last_name' => 'King',
                        'email' => 'johns.percy@example.com',
                        'city' => 'Edwardotown',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'username' => 'hpagac',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U',
                        'password_hash_type' => 'argon2i',
                        'password_plaintext' => '[dvGd#gI',
                        'first_name' => 'Donato',
                        'last_name' => 'Keeling',
                        'email' => 'mcclure.ofelia@example.com',
                        'city' => 'North Elenamouth',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'username' => 'georgiana59',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => 'uGZIc|aX4d',
                        'first_name' => 'Maybell',
                        'last_name' => 'Anderson',
                        'email' => 'cassin.bernadette@example.net',
                        'city' => 'South Wilfordland',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'username' => 'howell.damien',
                        'password' => '$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs',
                        'password_hash_type' => 'argon2id',
                        'password_plaintext' => 'nF5;06?nsS/nE',
                        'first_name' => 'Mckayla',
                        'last_name' => 'Stoltenberg',
                        'email' => 'conn.abigale@example.net',
                        'city' => 'Dorothyfort',
                    ],
                ],
            ],
            'wh_user_session' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'session_data' => 'a:1:{s:7:"last_ip";s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";}',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'session_data' => 'a:1:{s:7:"last_ip";s:13:"107.66.23.195";}',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'session_data' => 'a:1:{s:7:"last_ip";s:13:"244.166.32.78";}',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'session_data' => 'a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'session_data' => 'a:1:{s:7:"last_ip";s:14:"197.110.248.18";}',
                    ],
                ],
            ],
            'wh_meta_data' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'meta_data' => '1f8b080000000000000365525d4f023110fc2fcd3d9a4a7bc7570951236234011340455fc8c215aea1d7d66b112e86ff6e7b7211e3db4e77ba33d32db0987d599630b4e525413d60dd80294322453dc19a3dcb3a0ced2c2f14e41c7948628632bde752e21472c115fae118b076af8b34c0ae1f1041b1d18a8ae8b34fba51deef5cb83eb9307d12bdcb347f7a4de96cf03859c6c3d9f8c5dc8f6fa2a7c9c3deb44abe9d1c1a34ff1825afb7b3974ccacddc3c9bddf0f19d8cf2e5dbfe6634b7958f33cd4506365bb8d2f093999376fa8f682408e5f8c1d551d4b0d96bb4ae949d5eaabbeab0c1d05a14d62deac46d8646ab2d9412aa6c0c4938eb12c2d0d469e9b85af262138e9a0cf11c840c35f56fbbd24a61588a0d487ecd0f901bc9b1e29587d016aeac270d74a15d56ae75e1bbc77a33346c86fed94c1c2e7864b9b542ab450aaeb297f821c048e0b64f4e85a9c3d224c1a4d5c231c5ed8e9f7f26110709f2fb1912741220711777086e605f78fef11bb387fddf33020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"139.81.0.139";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"139.81.0.139";}}',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'meta_data' => '1f8b080000000000000365525b4fc23014fe2fcd1ecda4ddc6a5842851319a800920282fe4cc55d6d0b5752dc262f8efb693458c0f4d7a6edfa5a74023fa65684cd1965518f581f67c4c28e219ea739af40ded52b433ac945030e4421c5194ab3d1322cca0e04ca29f1e0dc6ec5599f9b0e7000228374a121e7c0e702f2806dd0b3bc0177a808395c88aa76546e6b78fd3341acd270b7d3f19064fd387bd6e576c3b3db448f1318e9737f3452ec4e6453febdde87185c745faba1f8e5f4cade38c739d83c9d7b6d2ec24e6c49dfd6bd402b8b4ec601b2b7294f45bed2b696697f2ae4eb6287ae7a5b1ebc67187a2f1db162a01b5378a049c5531a6686695b04ca6acdcf85442112b800b7f27ee6ddf949421a47c03825db303145ab050b25a832f735b3548b7aa5436afde55e9aac76633c46f86fcd94ce4075c6498315cc97506b696173b10a0d8f7764e4ab96ecc92380e71bb1d4624ec741dfe1945e429f0ef6788eb19e704773b218e9290443d7fdcc8f11b4093841d36020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:15:"187.135.239.239";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:15:"187.135.239.239";}}',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'meta_data' => '1f8b08000000000000036592dd6ea33010855f65657159116ca0818922f52fca6ea5d52a4bab46bd89066c821b302c769246ddbcfbda6ca246eae5f11cfb3be31984103e34444036e240c90421759a01919c4c2444130d0990ad16bdc246102b2905b2166dbf96a8304ec97f47875aefdb9e3b99daeb1ef6eb5631e9eda634f59a697265a6f4aa9b522f7b7e7c142faff36c3edb67ac624fb37dfcbcf636f78b261ac50f7fd4dbb21885995cee65639eee76f9c3aff47bf07b4bcbb8da2c76b3d1624871c15c55a8ab953974e214e6c4e65f8c5d8d5219f16e864a601b9bbffe28fee232e2e79352f6daaccecd8e81fcc4432eea7a680c488d1755fbf4ade2a2d7ad723206221a948395596f61b152f9b9fb3b2e8c1137e21d9bae16be124302fbed853487016d4959bb35d5b7175997366b8dca663a9e67c3dc6cd897d950abb4b09856ad381a744f5ddb1c08d479c7a7c0b27385d04a1a320af1b82c20ba0e72e0110f80276109050b02887228699158ee053a7468fab926d1790b58e0533f4e7c1ad92d381eff01fdaee8ae4c020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:4;s:8:"username";s:11:"georgiana59";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:10:"uGZIc|aX4d";s:10:"first_name";s:7:"Maybell";s:9:"last_name";s:8:"Anderson";s:5:"email";s:29:"cassin.bernadette@example.net";s:4:"city";s:17:"South Wilfordland";}s:4:"key2";a:2:{s:2:"id";i:4;s:12:"session_data";s:65:"a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}";}s:4:"key3";a:1:{s:4:"key4";s:11:"20.1.58.149";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:4;s:8:"username";s:11:"georgiana59";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$SUJJeWZGSGEwS2h2TEw5Ug$kCQm4/5DqnjXc/3SiXwimtTBvbDO9H0Ru1f5hkQvE/Q";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:10:"uGZIc|aX4d";s:10:"first_name";s:7:"Maybell";s:9:"last_name";s:8:"Anderson";s:5:"email";s:29:"cassin.bernadette@example.net";s:4:"city";s:17:"South Wilfordland";}s:4:"key2";a:2:{s:2:"id";i:4;s:12:"session_data";s:65:"a:1:{s:7:"last_ip";s:37:"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8";}";}s:4:"key3";a:1:{s:4:"key4";s:11:"20.1.58.149";}}',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'meta_data' => '1f8b08000000000000036552616bc23010fd2fa11fa5336dd51a299bcc3926e8409d3abfc8d5461b4c93ac89d332fcef4b3bcb043f047277efde7b970b109ffc68121074a005463d20dd32f6086209ea31d2ea69121274d4341790516443ec1394ca13e5dc4d206354a03f8c02ad4f324fcab06b091cc8f75278ccf98e70d7c9a2b06122dc501176d63cc9de9789371f8ca6b13f9c4f16ea75d277dea76f27d52ee8617a6e7ad9d738583ecf1729e7fb95fa50c7e1688dc759fc79ea8f57baf271a3b94941a71b53287a3573d54eee808a0313869e4d3d8a18b67acdf6a3d0b307f152259b04ed58aecda69eb843d0787b808243351b411c6eaa18133433921b2a629aefcb548b209a01e3e5ddb36fbb9542b810b33d70fa44cf90294e5d412b0f659999a2661ac85c9ab4d8c9dc562ff566bc7233dedd66b08d34d59a49b149c054f6029b03824b6ce7ea94a98add52e16ec7c5b8e97a41e8e2d00adc68f8a506feff0d41dd14fa166f0f6edbde96edb8fc020ca4e19b36020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:5;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"197.110.248.18";}";}s:4:"key3";a:1:{s:4:"key4";s:14:"83.243.216.115";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:5;s:8:"username";s:13:"howell.damien";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$ZldmOWd2TDJRb3FTNVpGNA$ORIwp6yekRx02mqM4WCTVhllgXpUpuFJZ1MmbYwAMXs";s:18:"password_hash_type";s:8:"argon2id";s:18:"password_plaintext";s:13:"nF5;06?nsS/nE";s:10:"first_name";s:7:"Mckayla";s:9:"last_name";s:11:"Stoltenberg";s:5:"email";s:24:"conn.abigale@example.net";s:4:"city";s:11:"Dorothyfort";}s:4:"key2";a:2:{s:2:"id";i:5;s:12:"session_data";s:42:"a:1:{s:7:"last_ip";s:14:"197.110.248.18";}";}s:4:"key3";a:1:{s:4:"key4";s:14:"83.243.216.115";}}',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'meta_data' => '1f8b080000000000000365915f4fc23014c5bf8aa97b240bedc6c64a480c22c428448828c10772dd0a6b5cd7ba967f1abebbedc448c25b4feeb9f7776e2fd0807e6b1a52f4c10e187580264e138a78863a9c061d4ddb146d34ab4a100c591951942b58438a7e6b0ab4dec92a7332b18d1e546b5912ee6dbb38f144b7dd305ddc505dec4de6e3f9fbf374b478dd0b56f6a6b3af6cb798783cca4b33eb3f42b0187c8adb61381835f953a5462acac27e7bbeaac63d73e88b9764e618f88cb9cc41e74b735075b298a213fbc2a70ae0a5617b730afd966d87d9f5fabe3636295af14a9be5d98a7d598291f54e14157056b49407c60a5eae9d6a51c404f0c2bd89ed13695a6c2ae6cb95b5c00ddb835005f353299cc3fe73cacda1a65af3585626bfba2b989d2d3726479de3df2d88bb05b9b805b64a33adb92c971918a88762bb37c5ce1b9fb2725523028a4818fa388afc80f871dbce3f43040e81ffcf1fa2138004894f62bf15fbd8c6381e7f00628e8a6d25020000',
                        'meta_data_plaintext' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:3;s:8:"username";s:6:"hpagac";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U";s:18:"password_hash_type";s:7:"argon2i";s:18:"password_plaintext";s:8:"[dvGd#gI";s:10:"first_name";s:6:"Donato";s:9:"last_name";s:7:"Keeling";s:5:"email";s:26:"mcclure.ofelia@example.com";s:4:"city";s:16:"North Elenamouth";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"239.27.57.12";}}',
                        '_meta_data' => 'a:3:{s:4:"key1";a:9:{s:2:"id";i:3;s:8:"username";s:6:"hpagac";s:8:"password";s:92:"$argon2i$v=19$m=8,t=1,p=1$QXNXbTRMZWxmenBRUzdwZQ$i6hntUDLa3ZFqmCG4FM0iPrpMp6d4D8XfrNBtyDmV9U";s:18:"password_hash_type";s:7:"argon2i";s:18:"password_plaintext";s:8:"[dvGd#gI";s:10:"first_name";s:6:"Donato";s:9:"last_name";s:7:"Keeling";s:5:"email";s:26:"mcclure.ofelia@example.com";s:4:"city";s:16:"North Elenamouth";}s:4:"key2";a:2:{s:2:"id";i:3;s:12:"session_data";s:41:"a:1:{s:7:"last_ip";s:13:"244.166.32.78";}";}s:4:"key3";a:1:{s:4:"key4";s:12:"239.27.57.12";}}',
                    ],
                ],
            ],
            'wh_log' => [
                1 => [
                    'original' => [
                        'id' => '1',
                        'log_type' => 'foo',
                        'log_data' => '65794a3163325679546d46745a534936496e4a76626d46735a4738784e534973496d567459576c73496a6f6962574e6a624856795a5335765a6d5673615746415a586868625842735a53356a623230694c434a7359584e30546d46745a534936496b746c5a577870626d63694c434a7063434936496a457a4d6a45364e54646d597a6f304e6a42694f6d51305a4441365a44677a5a6a706a4d6a41774f6a52694f6d5978597a676966513d3d',
                        'log_data_plaintext' => '{"userName":"ronaldo15","email":"mcclure.ofelia@example.com","lastName":"Keeling","ip":"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8"}',
                        'log_message' => '{"message":"foo text \"ronaldo15\", another \"mcclure.ofelia@example.com\""}',
                        'ip' => '1321:57fc:460b:d4d0:d83f:c200:4b:f1c8',
                        '_log_data' => '{"userName":"ronaldo15","email":"mcclure.ofelia@example.com","lastName":"Keeling","ip":"1321:57fc:460b:d4d0:d83f:c200:4b:f1c8"}',
                    ],
                ],
                2 => [
                    'original' => [
                        'id' => '2',
                        'log_type' => 'foo',
                        'log_data' => '65794a3163325679546d46745a534936496e4e3059584a724c6d70315a4751694c434a6c6257467062434936496e4e796233646c514756345957317762475575626d5630496977696247467a64453568625755694f694a4362336c6c63694973496d6c77496a6f694d5455314c6a49784e5334324e7934784f54456966513d3d',
                        'log_data_plaintext' => '{"userName":"stark.judd","email":"srowe@example.net","lastName":"Boyer","ip":"155.215.67.191"}',
                        'log_message' => '{"message":"foo text \"stark.judd\", another \"srowe@example.net\""}',
                        'ip' => '155.215.67.191',
                        '_log_data' => '{"userName":"stark.judd","email":"srowe@example.net","lastName":"Boyer","ip":"155.215.67.191"}',
                    ],
                ],
                3 => [
                    'original' => [
                        'id' => '3',
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a33383a223466623a313434373a646566623a396434373a613265303a613336613a313064333a66643938223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a31323a226672656964612e6d616e7465223b733a383a226c6173744e616d65223b733a353a2254726f6d70223b733a353a22656d61696c223b733a32333a226c61666179657474653634406578616d706c652e6e6574223b733a323a226964223b693a31303b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:12:"freida.mante";s:8:"lastName";s:5:"Tromp";s:5:"email";s:23:"lafayette64@example.net";s:2:"id";i:10;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Tromp\", another \"freida.mante\""}',
                        'ip' => '4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98',
                        '_log_data' => 'a:2:{i:0;s:38:"4fb:1447:defb:9d47:a2e0:a36a:10d3:fd98";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:12:"freida.mante";s:8:"lastName";s:5:"Tromp";s:5:"email";s:23:"lafayette64@example.net";s:2:"id";i:10;s:4:"user";R:3;}}',
                    ],
                ],
                4 => [
                    'original' => [
                        'id' => '4',
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a31343a223234332e3230322e3234312e3637223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a31313a2267656f726769616e613539223b733a383a226c6173744e616d65223b733a353a22426c6f636b223b733a353a22656d61696c223b733a31393a226e6f6c616e3131406578616d706c652e6e6574223b733a323a226964223b693a323b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:14:"243.202.241.67";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:11:"georgiana59";s:8:"lastName";s:5:"Block";s:5:"email";s:19:"nolan11@example.net";s:2:"id";i:2;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Block\", another \"georgiana59\""}',
                        'ip' => '243.202.241.67',
                        '_log_data' => 'a:2:{i:0;s:14:"243.202.241.67";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:11:"georgiana59";s:8:"lastName";s:5:"Block";s:5:"email";s:19:"nolan11@example.net";s:2:"id";i:2;s:4:"user";R:3;}}',
                    ],
                ],
                5 => [
                    'original' => [
                        'id' => '5',
                        'log_type' => 'bar',
                        'log_data' => '613a323a7b693a303b733a31353a223133322e3138382e3234312e313535223b733a343a2275736572223b4f3a383a22737464436c617373223a353a7b733a383a22757365724e616d65223b733a373a22637972696c3036223b733a383a226c6173744e616d65223b733a383a22486f6d656e69636b223b733a353a22656d61696c223b733a32313a22636c696e746f6e3434406578616d706c652e6e6574223b733a323a226964223b693a39313b733a343a2275736572223b523a333b7d7d',
                        'log_data_plaintext' => 'a:2:{i:0;s:15:"132.188.241.155";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:7:"cyril06";s:8:"lastName";s:8:"Homenick";s:5:"email";s:21:"clinton44@example.net";s:2:"id";i:91;s:4:"user";R:3;}}',
                        'log_message' => '{"message":"bar text \"Homenick\", another \"cyril06\""}',
                        'ip' => '132.188.241.155',
                        '_log_data' => 'a:2:{i:0;s:15:"132.188.241.155";s:4:"user";O:8:"stdClass":5:{s:8:"userName";s:7:"cyril06";s:8:"lastName";s:8:"Homenick";s:5:"email";s:21:"clinton44@example.net";s:2:"id";i:91;s:4:"user";R:3;}}',
                    ],
                ],
                6 => [
                    'original' => [
                        'id' => '6',
                        'log_type' => '',
                        'log_data' => '',
                        'log_data_plaintext' => '',
                        'log_message' => '',
                        'ip' => '',
                    ],
                ],
            ],
        ];

        $expectedOutput = [
            0 => '[WARNING] table "wh_user" column "username" could not be updated!',
            1 => '[WARNING] table "wh_user" column "username" could not be updated!',
            2 => '[WARNING] table "wh_user" column "username" could not be updated!',
            3 => '[WARNING] table "wh_user" column "username" could not be updated!',
            4 => '[WARNING] table "wh_user" column "username" could not be updated!',
            5 => 'done',
        ];

        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../../../build/development/userdata/.env');

        $kernel = self::bootKernel(['environment' => 'test']);
        $application = new Application($kernel);

        $container = self::getContainer();
        $connection = $container->get(Connection::class);

        foreach ($expectedDatabaseRows as $table => $expectedRows) {
            $result = $connection->createQueryBuilder()->select('*')->from($connection->quoteIdentifier($table))->orderBy('id', 'ASC')->executeQuery();
            while ($row = $result->fetchAssociative()) {
                foreach ($row as $column => $value) {
                    if (!is_resource($value)) {
                        continue;
                    }
                    $value = stream_get_contents($value);
                    $row[$column] = $value;
                }

                switch ($table) {
                    case 'wh_meta_data':
                        $metaData = gzdecode(hex2bin($row['meta_data']));
                        $row['_meta_data'] = $metaData;
                        break;
                    case 'wh_log':
                        if ('foo' === $row['log_type']) {
                            $metaData = base64_decode(hex2bin($row['log_data']));
                            $row['_log_data'] = $metaData;
                        } elseif ('bar' === $row['log_type']) {
                            $metaData = hex2bin($row['log_data']);
                            $row['_log_data'] = $metaData;
                        }
                        break;
                }

                $this->assertEquals($expectedRows[$row['id']]['original'], $row);
            }
        }

        $command = $application->find('pseudify:pseudonymize');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['profile' => 'noupdate'], ['decorated' => false, 'verbosity' => ConsoleOutput::VERBOSITY_VERBOSE]);

        foreach ($expectedDatabaseRows as $table => $expectedRows) {
            $result = $connection->createQueryBuilder()->select('*')->from($connection->quoteIdentifier($table))->orderBy('id', 'ASC')->executeQuery();
            while ($row = $result->fetchAssociative()) {
                foreach ($row as $column => $value) {
                    if (!is_resource($value)) {
                        continue;
                    }
                    $value = stream_get_contents($value);
                    $row[$column] = $value;
                }

                switch ($table) {
                    case 'wh_meta_data':
                        $metaData = gzdecode(hex2bin($row['meta_data']));
                        $row['_meta_data'] = $metaData;
                        break;
                    case 'wh_log':
                        if ('foo' === $row['log_type']) {
                            $metaData = base64_decode(hex2bin($row['log_data']));
                            $row['_log_data'] = $metaData;
                        } elseif ('bar' === $row['log_type']) {
                            $metaData = hex2bin($row['log_data']);
                            $row['_log_data'] = $metaData;
                        }
                        break;
                }

                $this->assertEquals($expectedRows[$row['id']]['original'], $row);
            }
        }

        $output = $commandTester->getDisplay(true);
        $output = array_values(array_map('trim', array_filter(explode("\n", $output))));

        foreach ($output as $index => $message) {
            $this->assertStringContainsString($expectedOutput[$index], $message);
        }
    }

    public function testExecuteDispatchesExceptions()
    {
        $this->expectException(MissingTableException::class);
        $this->expectExceptionCode(1621654991);

        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../../../build/development/userdata/.env');

        $kernel = self::bootKernel(['environment' => 'test']);
        $application = new Application($kernel);
        $command = $application->find('pseudify:pseudonymize');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['profile' => 'invalid'], ['decorated' => false, 'verbosity' => ConsoleOutput::VERBOSITY_VERBOSE]);
    }

    public function testExecuteThrowsExceptionOnMissingProfile()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1619592554);

        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__.'/../../../../build/development/userdata/.env');

        $kernel = self::bootKernel(['environment' => 'test']);
        $application = new Application($kernel);

        $container = self::getContainer();

        $command = $application->find('pseudify:pseudonymize');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['profile' => 'missing']);
    }
}
