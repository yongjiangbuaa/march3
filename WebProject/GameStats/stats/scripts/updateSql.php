<?php
/**
 * 
 * User: yaoduo
 * Date: 2015/6/18
 * Time: 16:00
 */

defined('STATS_ROOT') || define('STATS_ROOT', dirname(__DIR__));
include STATS_ROOT . '/stats.inc.php';
$server_list = get_db_list();

$slave_db_list = array();
foreach ($server_list as $one) {
    $slave_db_list[$one['db_id']] =$one;
}


$infoArray=array(
		's10'=>"delete from user_item where uuid in ('de94933ff2e64949893c3288595237c9');",
		's112'=>"delete from user_item where uuid in ('4f199284bf6947d0839882428e0fb57c','559434e0b0c84c6bb81e48a2f3c22f38','57b26076a46f48009903058a531aef47','623b11d996634ec78b4c52975c600de8','18708dd406614c6caf08427fd9539380','dc5c41629d4e48fc9433203a6297d7b1','319f257909db4227a6726947837a0306','ee5512b28f94436998595c3afaa51494');",
		's115'=>"delete from user_item where uuid in ('ff80d95cbbba4a76b19e6572ea5d0d37');",
		's12'=>"delete from user_item where uuid in ('ff464f05c0ee46329ce7d22cb0cd693d');",
		's120'=>"delete from user_item where uuid in ('59377b0a599d4b17936194b5045025c2');",
		's128'=>"delete from user_item where uuid in ('d3ab1e2e2024424e96d140baddc7b2af','4b2c88716122430eb433bafd4ef0b4fc','04a5fdc0065b443b9204e7b17d9ee8d0','4ff0522383674f9f81fb72aa0616e678','bc7e077d5a51402faee555119c84c85f');",
		's136'=>"delete from user_item where uuid in ('d3638f5cfd4e4d8989a9ac0b1e038654');",
		's142'=>"delete from user_item where uuid in ('1c52287963dc47089ff5f8298f7d46db');",
		's146'=>"delete from user_item where uuid in ('b59aa73043124b8987afcb41e0e9b1ad');",
		's148'=>"delete from user_item where uuid in ('0102803b34c247dcb8f83ce5844d668b');",
		's151'=>"delete from user_item where uuid in ('8b57cde3f70744a3a3632d579de42361');",
		's153'=>"delete from user_item where uuid in ('f02de7f37c1d4df0ae17499707c96143','8ab5c303257d41b98ae11a5c6e045ac0','b7c8d6596d464702af39ffffdd478827');",
		's156'=>"delete from user_item where uuid in ('21c9a0f611914165ace0df0feb75449e');",
		's158'=>"delete from user_item where uuid in ('05ea9f35327b40ca8a04544e02a2abfa','55d32a9007f64010a3ec5122b866e0ae','ad94113ae2254991a652497ebac58493','094f0061b3e44f2e8e7957a35a88bafe','94606f2ce5464e658481855b0c65ec33','188a683523dd419b9da19aaf17b1c7eb','0ebea566e2e64985b39c0b3571ea928e');",
		's166'=>"delete from user_item where uuid in ('b51635fc8e914a8788b61bfa93cfcfa2');",
		's168'=>"delete from user_item where uuid in ('7b06e62f59b043be96998a4e8213b27e','c9948551ea5a4623997182384457d155','d618ad5b7df84a5081892668e8725047');",
		's173'=>"delete from user_item where uuid in ('629e3179e9cc4b459f2e081d9c673b9a');",
		's185'=>"delete from user_item where uuid in ('a404ec921e1544dcb512f263977a73fa');",
		's186'=>"delete from user_item where uuid in ('d0802f84f80944debfdfea801c7d18da','b30643e8eac14af084a3c3ebb60bea02','219552f0540c418d92c9fa118fb65976','63ccdeff39104c33aa83c75cfd261a9c','3ad0c15c275247a2b7b661e5586ac0bc','fcab53bd45654091a159669f1d8a2200','763589dd08a14cce82f5abf2509768a5');",
		's190'=>"delete from user_item where uuid in ('2dccf09648bd4be19e57919379c01ca2');",
		's2'=>"delete from user_item where uuid in ('9b93530bcd114e7983b6b0e364dde7a7');",
		's200'=>"delete from user_item where uuid in ('5e940ba14b2949edaeb3883b4f1763dc','ce778e3aa96a43bcae2639b8608d816d');",
		's205'=>"delete from user_item where uuid in ('2653575de99449febfacdc397096f3ad');",
		's206'=>"delete from user_item where uuid in ('8861341e68734e1c8b2a88e6d8ba852d');",
		's21'=>"delete from user_item where uuid in ('70ddc06a54e84fc882fcb9d55c0e404b');",
		's23'=>"delete from user_item where uuid in ('bc834e4093c7429e8ec31da5619b0d6a');",
		's232'=>"delete from user_item where uuid in ('17887e7fe322472c8a50a3c00c09c915');",
		's239'=>"delete from user_item where uuid in ('1518fdbfff0048e2bd01b8514a5ac561','180d0d9e9ab0477a9d00a127a8adf648','a881caff7e084e31a2dbfa1649c9d301','3d518ece82ba4189b607071965edfb93','e6bb36b270d3421c9f5b071c732ab0f5','01e3f26eee58429d8c677d7087b006e0','f21d8a5e8b644121a00c0a0fabed5404','a3661b39713b49ccb5e426f9d620e069','d4a6f878bdd04322b6959932a8a7c2e6','fb71660bac9348f0b8d263ae9ebf31cb','941ffd7d66564e2783c2bfc950af0457','24458791754f474cb9e3601454b86925','f07635f650a54760aeb779d65610f66c','99481869a9ea456c94768eaa82f30c34','7d4bfed58e964e6fa5ffc66481cac6e5','8a61b3783fbe4c9cbe58fcfb815a63ee','6c60c4962bfc4f80a0215f5616140bfa','c09162ad56e5485fb3f3ad259264fd69','2fac5a77f15045ac9ec24c26462f5180','3fa377c5f9fd4addbee34d2a1eea380d','a9d67b98baeb4fedb0ce15795f2a69c5','e2024bd402774efcaa4322c1a9127e6e','f7fc0724d1304b629a20849fd817174f','adb777913d764670a2096d23135e2ac9','da5248e07e784b64ba201e03efe6bb66','86db4831462f44619e9e9b1d7423f6dd','3ebaef9121fe484ea4cbc254033551c6','96fcb9d8a6774929b1042c9ed2e4d314','2ab21aa79d5e4b2c8620e540231d7b73','a16b76fd47b4449fb6dfe64c0d4d1ea1','50acf94b96fc44ca8f0609c97a348bf9','f628c185910846da8aa9c6bb37adde03','4227f24fafca4a01a6a815de98bf5058','ff0697fe1a8d439c8e00534d4a7c8e6f','3ae2569a3b044477b8fb7ae28c9fe76a','a82d801a47d94d169aebe973f4343cc1');",
		's250'=>"delete from user_item where uuid in ('ac682ab8db2141ceb61bc1254a7de0b7');",
		's26'=>"delete from user_item where uuid in ('02795dc2ffe24143a187762c2b291df5','53625b4dfb4442f98781f29e525aa50b');",
		's262'=>"delete from user_item where uuid in ('13f159f6acb54d639b3db679fba62154','f48b9654b1d24a28a0658c5b6626fd6b','4d6044dfccf5430794c0140c7f329c08','78f45008c927402e8dc111ad94b7837c','d07f0cf7364c42fd962e4685b739810e','ad5053d6e7174588aaf84fb67a5888d3','8419ebfe8b904eaf83f4fc1ce7ae57f6','7146854d7b9840c9a21d13df26cfe269');",
		's270'=>"delete from user_item where uuid in ('3bcc24400ab34359a199b6782b873bf3','a28b5e954e4d4acc94b8255cf730dd88');",
		's271'=>"delete from user_item where uuid in ('9f1550c7ef854d34888cf028a5824ac0');",
		's293'=>"delete from user_item where uuid in ('806225c3782a4724b904c68ddae78cba','6334aee7c7f248fe81f6a80944678d9c','c6801524435e454bba752a83af2d553d','55b1f454e03a489ebf8605647be04ccf','56c8f4de58a5443c9a94d363185a1fce','9dc3021712b84263a4fcb2e87b608623','507459470582439e947f92ced84f4146','bc656fa1239f44788e4570bd0af8cf2a');",
		's3'=>"delete from user_item where uuid in ('5800e885ca854631907352200737a005');",
		's300'=>"delete from user_item where uuid in ('8b2ecfc2fab140a4a6bf12ee618e363d','d5015441cc9748938ad2627f01b3b5e5');",
		's318'=>"delete from user_item where uuid in ('2b302e1d076a47149d2689793b741eea','5e79e26292f24de9a92222f07eb42c45','a258081f0daa4da49b97914c95485151','883c1d68578d46f799855ee640cfaca2','b7d43137edab4f1cb1cf8b4ad29bc902','7fed4868740f4a299780a93b0ccc1f7a','7cd591c6443b4a0f99213a6d0c3d6381','324be9f691354b98a086e5e057a6e58c','3c3170c31b474227a4a1bd695a375191','344f2065640f454d80e67d0f252ab49d','380d207d114e4098a40dcb99160d39ea','4e6b149ce6024b3480dbe53c39be68f6','c189fefe7b04420ebfe05ec4d1b6d482','6af2333c77b74f53b208f132e686626c','60ee0b3b298e4764b9503654967efd42','75017032f7254a4784f78ae03ecda51e','01378270a2a94ce7a3524d25e4590ee2','61827e7a5ca04c068f8b143557ae6ccf','1ab87e8c42bb4db89fcda28459f5dca8','e94781c4eb42451db2d981afd9a1d9ec','78739fbdc8d64177ab0f07978bb8e663','54b4173494264d0db74fa25476b6680e','5444d6f8fb32401998d4bed810a6a9c0','33cbfdecaaa143688f21b49817481740','d35ffc0db85a4eceb51cbb8c4912c901','2a8bab36bdb24aa490b2cac0f7abb1a7','0ba1196347ea4eaea1557eea5929934d','cf128eac531d4816bb71ce89a120f854','53d3e46e5a3c4eb28799298732e85b12','b15339e750564181bfea742241e719e5','6a69e9cc1bd3444998bb44a4464e373b','0f70799dbf1f477781dba1171869942a','58f319a405fc429989ebf2c035cc0ba0','16354ef772ea4e1d8f08d09599365c5b','4761546cace04d6f80a1347620cea4ad','1b9003ca74a9401db976c6a77438744f','2bd4fe2c9e294dab83f13e234d9c4a13');",
		's324'=>"delete from user_item where uuid in ('627c917784d64282bc3cac2d00651cd8','6bd818684d704208aa27c5e6694853b8','9ca13d8538064fa8aff6983d67c1572c');",
		's33'=>"delete from user_item where uuid in ('56ef743b8bd949279c818e9378c3411b');",
		's333'=>"delete from user_item where uuid in ('c1988371e6fc4ff19aaa591afb320ee2','44e12cbd4fae4400ab28b20a21a9151b','c737b217282841168e0d9de1f1ec7c6a','550619bfc2ae419d987cee5b829011e1','5d98d310e117472d81cac84b13d2e050','4eeae3f059614966b5066c6c7caf62ee','039b77a5709f47a89dffbe77f1114f16','be90551f9c0d47b9aa6d009b3a3e33d3','1021e814df3e4092bded76453e93adfa','15ef477b2fe84eec9b1a7fa3b4285c46','6ab3517fceed4fc9acdd78ec312f59ed','f56e7b25002d42509f0a9464a754a318','96fcc1d04d4249478cb104a09c59f26a','bd14199f9fef4a1e9e8cf2581d997f10','0e046b23989d443388cec8f6b3d8a18a','d7557a3e3cf14366a414a07bbb6fddd3','7b037de5782c4f5cbe9baaece8d6f848','677b46e1ce524d79b7a49b3da9012809','122c591e03c34aa68c3e7e2ebbb3a148');",
		's34'=>"delete from user_item where uuid in ('71c57da1fc954661a5a87be996eec5f6','7f2d9f4eee034c9fa9bddcff63c35533');",
		's347'=>"delete from user_item where uuid in ('fa1ba6164dce49138cf6225ebcb58d12','bbb7a6a1d943477983ba7eeb6834bd8e','1f8ae8880b6c480295eb275567b5cd8d','59371100f94b4d2d94a957ed7b6cb6db','025115cfefcd4d618097a55b22ef80b6','0a6cf82326d54d3784a00d5e94455b8d');",
		's368'=>"delete from user_item where uuid in ('d2d00e411fc9493bb83f57cad04f4549','a529596ca3ca4082b664322e488b353c','2c3f5bef75bb4c7c8ef8a878bcc079c1','7672331e48fd4bae8bcfee7b02183464','b90465fed9e145c7a9c7fc13c9df2437','e8633309c902418cb7cb34cd89fbf114','85586bd353974d3e98e205f258179fe1','0aeac426a5124783b2e17de0ef1797d5','4f2a7b76180c4f58a5c00500e5f56219','c5052718a91f428e9a7d7fd9640d73db','18910605a9eb4f05933d99a0b8e7cf34','4807323f34c14bb2b56b0ef49ec07fc8','7a629761a474424f873078584dee9461','9daa6400c6ee458d9ec3f96fdb99f217','6f3406a637464f17b0b9d02b45220670','aa550b010352484da5c8f8262e800625','56efe09e2f9549c39196ec2b132792bf','46a09394a99a4b29a8212ca0943d2f9a','e43ef7f4afe049b7a46eb712dc13592f','613217de42ec40e493d998305194a48c','ece69666f55042798a80aac00902c2f6','9a416f3832f34cd388bdab552ca29939','cd5802cb93cf40bf905bb177c1700891','6ec0197b95124679949abcd33cc3e368','0323bb42005142bb9a8d841cdbd11c4e','72aa03812fce4912aec13ffb1ed1bd77','67971e6133de4207b87378090f8ef45c','7d1ea5108a01403b85e8cb3b48964b3e','242cb351537442c78c0a0285d2e34260','4af4ee9e4895442aa74b831f7a872c43','ba8cda3834394470b721e963669201b3','93eae54b990346208dc45ff1f2e40295');",
		's37'=>"delete from user_item where uuid in ('452544f7e9194b5db4754e01c665ff3f');",
		's378'=>"delete from user_item where uuid in ('74cc4b5dfc684afa9d78c7498fbfb684','83fe86b2f6e24c89babc1c670492bb7d','01edefa6eb6e44c99129162be9d19844','0d39b1b46ec849e2badf208860fca22c','52eee272a66f42588a5f635e903feb73','7ef110516df4414ea197e6f263377db2','6ccb1b86254e4ec496f4f2fbd0f30bbb','0aa8ef0aefdc454780a846029f76e5f4','9dd71f29299a4f62b798ce979afed042','ab60e582bedf430e8f54dba0d400abdf','ea7c9d73d7dc46e68031c7e43147e32a','4b6eb715ae4d41a2b7cc05f5ec2337ab','8e5814b0e8db4c4da610d51b90f2f7f2','08f1f265ea2e449b8fca2cf3ac1f56ad','69dde8b8132042b3b3354cdc10158f3d','0e1823d3b6384e7fa24b298bda1d9867');",
		's392'=>"delete from user_item where uuid in ('7383ddb80d754f628a2c294013b0c3a5','830c1c2f3b1343d1b66130f315878fbf','29c4bb202b664cd9ac6c97849cbe748f','160adb80d36c4fc7957b4c113a109820','766f5a3707144f33ac6d356f91110b47','fc050cc4282c4cb189ac9d31c02bf5ee','e0afbefa3340443b9c0ba18defc172e9');",
		's40'=>"delete from user_item where uuid in ('8e877514ce704a408d2187345c15c1b2');",
		's402'=>"delete from user_item where uuid in ('e471c877a315480d9b3796bd63e0fccb','25d17777538143c1a873d30f0c1b4ca1','2a349dc89399432a8be567da8a4d9813','33afbfda3937415eb096d13d662dea8e','38814e6f78864d59aa6ee7824b01a177','9d303ecf3a3a47a9900c33717be3b5f2','cf052ca356764f2da1b019d3cf94c4dd');",
		's408'=>"delete from user_item where uuid in ('51a2541529744aab9ea126a8b441fb7b','af8e446d3e4f4740bc6a5c98bf3536ff','a55a43fd28e645358dcb1a50ee32e430','b223d11bdb3649b19f5eb20f41fa353a','ca6e44e82cea44f3b3142d89489fcb83','0b49f07ad70443e6a5e8f789ca86c557','789efb97e6344bbaaf4d549cf7208840','1e317514455f4428a2106dbe7856dbf4');",
		's43'=>"delete from user_item where uuid in ('829b76d2ac524cf3a0ac29c817f08f9c','4d9e227c659041539e90201bf268d88e','cab8f9543b3a4625bacec44daffe009f','57062179d4db4e3d91095b255e5791ee');",
		's437'=>"delete from user_item where uuid in ('b7d5bb9ba2684f558d4499157b7427f0','65aff30d61564440b709ab9c2e4210d1','538031516807447a84e3fc2ec0aa5a0d','d2acf112224e443eb18aaab5ba51b752','e954e3fd9c8c4412a45dc761c56a50c1','9a2a595f0aec4ac9a31787edd4df2934','aabe0c2d70cc419e99d280d3c1da08bf');",
		's44'=>"delete from user_item where uuid in ('0009dc2aeb01472098f2242516f42352','618408699d714beea04400367d7fd92b','86ed1267b08b4eebbbd912412ddfcbb6','8087ff9d1cd54af980e4076e9c3d392c','c3196b6484db4530997a6fb7e7c25e13','c2010310a0a8412f89ac2311853da371');",
		's454'=>"delete from user_item where uuid in ('d7e8235415444b7eb94a414f3b18c4c6','d7210c717542433f91c08dab7fc2c0de','93424f74920445e5b0d2015ee41913dd','62ae2f0fd3b041f492691764679236eb','d08574c4456b46e88274b776a26dda47','1d95584c711649128d43e65e4dbd6ad3','8714d963d40549e5a33421193cb1b1cd','bee1f43a882f44139bb352cfe0182028','fa3986f060054c7e88954d4b523e1ecd','0b03771533fb481a8397bbd838921973','f0dd5419a88b44008beab5a2e4cf6408','0ead60c0c9df4b909e8520d2cd3e5ffd','79a4cd50c31e4f6b8c3b027fcc2d6714','3304c573711842dd938a6451dca08582','9715ac888a884c32ba4e8444bc18a9e4','cebae66de57f47e78c5648eb199c2efb');",
		's456'=>"delete from user_item where uuid in ('57b94105bd08486ca74bdb01f31e1853','35cfebdc728342a683332f1c1b579e8c','f03bdf154061432db9394e7f4acee732','31bd50a8f95b40f596ec3f0aeb1a433a','f32160c4d2ad4ab09ca620024625d9d3','94f59aead21946bfab667845f98084c0','6f727ac98d114875a1974b0750dbbe88','fdbdcb52768d4b5eb85d8bf29ab73b67');",
		's463'=>"delete from user_item where uuid in ('1e9e81b928eb4bd580853e89fae2309d','05ef53e7dc0640ddb5acf9fb56308bb2','e2834bf024c144a7bcfbf3fdf63f47fe','16dbafe4a3ec4defbd7688fb5242d208','bdd5b4ae1b40419ea567702d7ac7fe8c','88907edc2b35403da70e2373159a1fb1','73171529943d48518ca71d567c02cc10','c0130096dabc4d5d8d561e99765789d0');",
		's464'=>"delete from user_item where uuid in ('aca7255ca9064e7faeef11102810f551','e6bc3fff7ac74b6abafc9112b06f9293','8db549ce616e49bb85b9b468eaa465cf','2db4d4806aab471e891a039c7ed827c9','397a89ef913947daa1fffdcea0d07a2f','9dbc074e4b814785b42a702dab3a7610','f7c4cdb7c2284dc390b6fbbfc9f20a96','572f09b571414bac94fad3ecdfc48af5');",
		's486'=>"delete from user_item where uuid in ('fcf27727976349a78b1f68d4b7112e59','cf9bb784c9ae40f9b7189c1826da4aaa','8cb41431a1a9471b99e891c05930ea9c','43464a7de6ed44659c8f6cc7b0d4abcc','6bc15a13222f4869b2dc2e5cfbe72e94','228f3c9aacf1421b8c768f44d15b7b79','98091ad401c1439eb4a60600f8c629f5','fe241f8bb4b947a397f4812f8e8ba13d');",
		's49'=>"delete from user_item where uuid in ('5e7c7df3801b41b0b993a81be3b8090c');",
		's492'=>"delete from user_item where uuid in ('2d27bbf09914409592fdbbedcce460e8','f5b2c7216fea4d72ad69beda2b5f115f','c0fa0b19fb2d4ca0bed05d2d7578a173','58c62c5a9e004920b3b9ad44324b91da','cf21f93afd994108829e26ea0496dc1a','dbb4b009b8e44a5db8c974737b763dee','f2f7d2799a9c4df195d8638a9243c875','7ada8e79cff743148d11aa5a6eafa29e');",
		's494'=>"delete from user_item where uuid in ('b0e89cfa1c1b4c77aca410ac79ce6778');",
		's496'=>"delete from user_item where uuid in ('684d5817d9c24301b8c32a2c436603d1','48b97fd7d70647c48839f9ad672ec21c','deb7b3d0e3ea4b8a94060d1fa62a34a3');",
		's50'=>"delete from user_item where uuid in ('162225d9d97e41aba62a98b2c6c05ed9');",
		's500'=>"delete from user_item where uuid in ('36cdc09897e340d28a404452e349fb83','698bb0648ce642e5afbcff834a4815ad','0988e0f2a5b644baa3db0f9228414489','04c64472027e4630810af1d2ec81b816','2acdc9690ab94056afea260a6a31b8d6','0f24bcd77e5440ce8726271e38556dfd','df7b589371b84d0fa7d1b73c6b879ae0','2785358a27e54a08a74acbd7cd2a3382');",
		's513'=>"delete from user_item where uuid in ('0c3981163c454745b34fa3b345cc2d7c','2efad8bf8c8243e093fe0fa4ae07f91c','97c9e21cba994b1fb6ebe975eec4b05e','cf331e39275649b381425a92aabdbd13','af39ba4e1317485da511d36ca81057d9','81f0bcc58c824f0eb88b95849675aaba','31799a9d12c84b3b92ea6edcfe0c3661','f7fff81d9eee472b8760d07948598330');",
		's517'=>"delete from user_item where uuid in ('6bcd0ed30752445c95961b2ddced14a8','b174b90a5e21483e833a0c5360d11a50','ce6185f581e5411f84dea1cff6b01186','af0c771dde674317a841acadacf64338','1156d738a13347328cc9f0b2fecd312c','e812a5f2aa574a8a9d03448c7826cfa6','b9466e4f73c942a18f3ea936e22f4552','70145df3f05944f692895bee42a6f44b','ce169d60fc3a4643a34e0498a776b551','0c7b2e665ad447f198b2f5ff9d45165b','886b56a57e264a7881ad2f2e5b5dfe5a','3f762ef77cf54f9d8bc7d9ea056985d2','1bec398e4dda4560b39a7a1d992c0c45','7210e94c261f41d0bd7efd99f1e57bcf','2c673ba4a0c34a0fac5947cfc421c55f','9027faf0b76e4d91bcbcb2da0a116fa7');",
		's518'=>"delete from user_item where uuid in ('18624f4c289844e3addbb635744dc521','0ef7e9c134114afbbb4d9165a425a856');",
		's531'=>"delete from user_item where uuid in ('08987bd07bd54e18a241e76db0afe496','3d1c06e947994a8dbc0c8d3eb27102af','c7fe8502742947e6a5ed5a916e693058','ced775baaa47436cb153cbbd83981cf0','5b2c7acdf5604abdbe1df928ff9f6ed7','61e3d420f7474800b7f75d7ea4a2406e','bbea9ae2494a45f78e7e4b11572f8204','3759550259f7413596730fb8820a5bbc');",
		's553'=>"delete from user_item where uuid in ('ae201b76b84e4af493b9e9702e32313b','19b661b408d04c739e2e0035d5921a66','57a47c71e2054a1e8ebafc68aef51ec0','db9bd4e7a721492588b10b2984448214','80b8aa0c3a924b1b9299375a51b1a356','1cb70cc651e045e6a6c0efac0a8c4f81','9c5be73ecd5e4042aecb14014736fbcc','7ef9ab3935a549a288decacb7ef2a243');",
		's554'=>"delete from user_item where uuid in ('99cc049ae97e4005b5fe28a0135fde82','f2b3b907140b4a31b15264902f2fa900','63c0dbf1d27343699c6e93dbb483f4de','af8f425ce3e343d7b673c6045492cf6e','f929534ddfe84863b858eea4e6454f06','ed408a7acb8741deb959a9c62983653c','9826510646804be39d1f35ef661f2d7d');",
		's555'=>"delete from user_item where uuid in ('8266b93adf674fcfb6106fda491885f9','207b4d1211694d63b5f3e1a33105a249','ad1a50065b9244d69ad780c949a90737','9b94122487bd4ad8a7589e8cb0d09d51','832b34700ac648e298a5898a2abcccd0','4575dd21e9a84108a9ee4e0e285574e0','7873ae5114144937ad72d3e38ab6e416');",
		's556'=>"delete from user_item where uuid in ('e46112cd362747c0a0bc724c14f878f1','3dd8a2e08f6b4ff1810218e81d9f8a4c','6e2387576fde4266b46b5a3061762b69','885f35896d3540aca5cfe9821eb54861','a10d9c53288f4e02bdc12edf78cd9c01','be8e8dc06dab4649b3817846e84e356d','cb23caa34ec040279e5dce892af9c57c');",
		's56'=>"delete from user_item where uuid in ('d6938173b76c465ca3a55c0ebcc99951');",
		's562'=>"delete from user_item where uuid in ('b2537e57eaa342be907f320268796ab7');",
		's567'=>"delete from user_item where uuid in ('9b1e979427104241958b74958bb66f3a','dffae88d60a44c9d9b54a27927fd44f5','9e9d0a4f8d6f45eda4790ba1bc37a5a8','ed175c0a332d46ea9158b2b2e4f66062','36e0df6903934ce2bbd840f02fb7d356','b39739d0cd4b4ef786c48b43ae7def43','1b929d305d62493288b135ea3ad9b4c4','16fccba471a743bb9109a7f510b72d4c');",
		's57'=>"delete from user_item where uuid in ('384ac7b712614dbca8bdbc1f2708018e');",
		's58'=>"delete from user_item where uuid in ('6cfd8d86bc9043debcd207574a9d04e2','1788bc7b0aca496cab59fbca9571bd8d','10dc7b22c5794fc587f2fef06e4fb666');",
		's582'=>"delete from user_item where uuid in ('72ec9fcecb7b4e5dbdaed374e08be9e1');",
		's60'=>"delete from user_item where uuid in ('7619fb45c96c45e3809c12b5dda69528');",
		's606'=>"delete from user_item where uuid in ('9762b55f194c48e588cd84f714a2df53','fb28253ed117434e95c19538f5d3ba1d','a488ee83f06747aeb41a3e912dd91884','ce3007c684f2421bba9aed40270a1e90','0722afb60edb48bd8ff038b644e39935','1165c11a3a83470190afe30e0e85f8b5','e3eec417a4e24699ad133e4eec01da88','53af8012e3514cdba8a78e9f3023c781','701f32c99e3c4755b9ccecc41ea5c63d','2e06f70c6b564e3cb8d738c634fd2441','23943d25ce0f4fb3b04ff8fb09d3a788','6631a4a309154571920cf7846584c41d','bbf2261d9c684757bb5ec66f2d92fb7a','5253a5a9e3d04d3c871957a13276cf49','317c109013ba4819a02bedfbe3088658');",
		's607'=>"delete from user_item where uuid in ('926559f4781246c2b150cc6ed3ae4fb8');",
		's61'=>"delete from user_item where uuid in ('b060038fde5649089f1005486f6c9953','fbf6de0d86d94e34ad4c04d38d705485');",
		's610'=>"delete from user_item where uuid in ('d78e3f5dd1d64d7b8560cb9d668ec8f7','d4938bfbe2b54b4b8b3495e51c10a66b','3144a3e9f6f1416796f5b8dde10e00e5','15a412fa4496495d84e1b18a07288c53','cc11e92770ff42b794ed4b9e37b68327','16582a70b99a4a969686cd45d43a7b31','5b2755e0a7714d7a955bbe871f364ef1','1bb25f89985943ce8c26aaa7ba5ac415');",
		's628'=>"delete from user_item where uuid in ('40f36a4d867845b4ae49f8d03eb0b7b3','d9169d2315ed49c080959077b8e6d68d','388dfcdb75d64d44b84cdffc4e300b59','5c10ca3cfe014c608aa4d0e7248027d0','3f37fe480681439a947341afd439b925','66e5cca7df3e40c6803c7f94e52261fe','e37ffc0aa12848e186dcd50fca4fe76d','8603c845f92f479fa8c98bc49c13b04c','0a95d70f104c479bba137f1487954375','f8fa2e16800c46649da5b3cbc682d544','90b8ebc374244d5bb27d78ebc347d8ca','ba66246d3dc348a0bfa51a95d337fb74','e963f8bdfb284f8c8d8992ef1cddd154','a6a4f87d45d34593af205490d8286e31','a3140cf0d30c485e99d4d4ea32c75382','991967ab954f4c1e8e6c8dbdcf133184','0bcb8e292d3744c19f7d8e9e39f395df','b21c2023e0264b0bb3ab229c3601f949','696bb9d7cb52458fb0fa64a8306ce7c0','cd4ae335a37342df8bc9e0e5a2026555','b9c75f35015a4bec884f50de2d519a0f','58efe6507f4f40f2863213731751dcae','76fc1725dfe449f1b24d61c05cfe4a62','2b86a62cf9354f3ebb58b0243fd0280f','b28e7443e22f4864bbe68a42ba5cdaa6','de1503305e27403fb78c3716de07fbd5','0a6603e3ad0243e1be08f52ef7b87f3b','6a773e4043664e759253bae1bec89548');",
		's644'=>"delete from user_item where uuid in ('d08a369ab5ec43689d2f0d5d73007290');",
		's647'=>"delete from user_item where uuid in ('67e172cec6ef4a1080054cd8d3a4919f','92780de7eee44956bcd1ddebc0c58c3b','b32ccf7150064482ad539816441c8270','6d280330442e461e8e612b0908a8e9c4');",
		's653'=>"delete from user_item where uuid in ('f9a598cc028745379392ad5c79b49bd9','c8c3f169ac224a03aa5bf1522754790f','66b94d2c42b3402ea334c8e4f8b487e9','a7d52a2e1d954b89bd1acf062e7563c1','cd099127baf8435c8f4bf335b946c074','4f9e2567af114b359d574d3e0af4ac51','29fb0838af1c491ea137838ad5a5c02a','79bbaed450fb4ebfa9a23c9e8497cf2c','05478d0eaf714eb697934d11921f81a1','768bf46ac0244992ba0b61306ddb6938','e925b729d0bd41e8a156bae8096d602f','30c98542d3b4487b95dfff8364643817','2c0c600d2958470fb8c1e9ea4e71eef3');",
		's654'=>"delete from user_item where uuid in ('57c36939195545a2a49f27ed2ea8a0a6');",
		's662'=>"delete from user_item where uuid in ('de492ed7de9e4fce93a0f46c1f4fb040');",
		's664'=>"delete from user_item where uuid in ('599ac9ff3bad49298ae8b258b8a7cc59');",
		's665'=>"delete from user_item where uuid in ('a3cc7a4ddecc44d3a52d6aa4a941a9ac','52ac7a6b35f642f3a1affb9f2b498fd4','91e647abf9b04db7bf27d07bee586c9e','2d5d7ce9fee045dc9539edf6e4ea7020','bfa006e06e5b436f93e129343538279a','83f8995dd3ae43f5b5a7b811842354fd','9092aafe89084c48ba1501f6acaa6890','f32e268aa4034ff6865c0f5a0c686e4d','14c63136acc14ba088628fac524a6281','6cc630f509c84f509adc9ac5cef367b9','b8f73d64c0944a8aa0d13c6ccc8d84e6');",
		's666'=>"delete from user_item where uuid in ('413463b233544e4cbc0b9d12c2515dc2');",
		's667'=>"delete from user_item where uuid in ('e1bfddd90b5e4f339bc37f17e59c3766');",
		's668'=>"delete from user_item where uuid in ('11be34a73f0c4ab0852d557d13a71dd3');",
		's669'=>"delete from user_item where uuid in ('f5d942c59c8b4bef9ca817461dfb9fab','b37ee598661e46339a0f16a80f3e9ad3','26a102eb6d304b4c9dc14c70113798c4','b2a336b603b9441aa83c969556caa9bf','7b50a87c7ff348adb9e3141df247fb9b','eeb09631097845fcad392e9faf234c0b','f24ac14ea3514395a8db1b6297493674','4814a2bff37c422f9762778af63aa31c','c6d83e9d1d914361865abc233f6be584','91d1bd990f384f7e97585404508992b4','a2d00658f34d47ffaf309860fde199bb','79daa58f3dcb4b089dd105175b149191','58f00951614a41909366b1fd7643d15e');",
		's671'=>"delete from user_item where uuid in ('071c1178aac849739e159b06d3bc2418','8c5eabb5de7345a59ae9121e09bad8a8','0732d8c56c234edb9d620099ab4152bf','5af5240e22144e3ab9d08087366480f0','129eb8e81a1e4ae9bec2bbab7ff39fa2','dd011c9cb01e454c960186269f74a842','63ae691d065e44ff9523e8dc33b38912','fdcb8ce3a8244112b141eae3ed3b534b','6299c5822925481eb49b8b1b5fb5b592','02f9de04b9724a918fb7c0578aad2950','ffd93d03ab7d459cab9c495eae9f1f08','2c31dbab053442df9c8c3bad6e5f4770','516b60885af643eda1fcefb9e7599561','0c5ebe5b444343bfa3536921633e59ce','12e0d558125a427ca874cd992de1ba81','c35768af9c0d4d13920cb51cf1111375','4138eb6b5a634c218343721e61f5e43f','39ec313715ac4784b6627b80a6d3a438','dbb69be72c084dd8baeccb59623d8f5d','6bdbd9b7d35d4b5d81df9ae3d3a01b0f','c751f49856c64730bc5063976b04ee36','a4601cc8890343e0bc7bfd292023f670','9f76460085454ea3b5a174808b2bbbbb','a358527f99c145349fa4a120608aa9ed','41244d9edec940d8a740d7bde2f715b8');",
		's676'=>"delete from user_item where uuid in ('484b1f609289449793494a641eef269d','061b1b80ac3c4e2db9adef971846ad61','69531d6a3e7e4e68a719a27cf5850f42');",
		's680'=>"delete from user_item where uuid in ('6bd6dcfb133f453f8cf0a8b52b0e0073','db76e5ab5da94b4ca37fdfdfe9af5601');",
		's684'=>"delete from user_item where uuid in ('b29bf7426c4948279b12392903350180');",
		's685'=>"delete from user_item where uuid in ('d659bc4b34d04efe80f765f19f4e12a6','50a9f4e96e4140e28ecdf5c859c4da09','e24aa5d11b404f269b2d9f7170860c1d','fa738cd8afc14b0c84d50db3f22ca8c6','905f7a7ac8bd407ca9236722dc31541b','87ab09827981421cbacc40d6aa93abe0','cbd88124f15a4ddd96f0a9d11645bc47','c7b365d9ff4b4ac2944b041854c8c551');",
		's691'=>"delete from user_item where uuid in ('1f12c7b8cd694209b5289a41b388ee03','8e3c40dfc4c14007ba2a16868d8ecc58','09ff662d77b64d62a124b1d09e3f549a','bd2d814ee4a24639bdf4c8031d4bdd10','d2b51b58d9eb445b8fe5d83cfbab225a','16bd9fe3bba341f5b08d82b937eddc8d','af50b1c0b72c4079924cac0c746f51bc','a10844b479c746c297b31691ce1deeb7','0c06ce103321496e89946ba3f61c49db');",
		's698'=>"delete from user_item where uuid in ('65eae6a6060a4b5da8f3428cfc19a91c','83a5225995aa4cba9d0999247040d15c','7d6092c426f9402f942f930466015418','7b3c84a872344ec9961f6cb298ca1c5d','c92c627ef6304502899f8241322cb559','0b976fc15910422dbb814f7bde479a2d','456f17ef6c6f4443a5455f73d6f3e7c0','9a08ad5bfec74c039aef00907640747e','17e0c2bb83364ef09b990471236808c0','387b85df68b244d58a25656954819f2a');",
		's700'=>"delete from user_item where uuid in ('d4d3d835fec84be796fe3cb394c90a2d','48a620920b1047c69587493a61e80de3','8325f05f8e834b7f8fb041686ae8be8a','64a5abcd50fe4d7daaa3c386058a17e6','82a3fa048fd34da3b0e4a8e421affcf5','2d83d16deb604b5f95ef5b71ecae1373','942660602dbb4c95a154c8b180ffcfc2','925864f396c540f4921ede8ffbde74e9','4ff74ec46b8144c5944f79390687db59','bfc1a17651ba4db297b0454fa775c69f','21601ca4a6a84f5591bc7ff0e98442da');",
		's718'=>"delete from user_item where uuid in ('48719a5fa2074c2c9de28b69d97d8bd1','ef9659407ec84c4980d8d0bd932f1cba');",
		's73'=>"delete from user_item where uuid in ('9ed1c2eb04584a87b790407e319dabef');",
		's76'=>"delete from user_item where uuid in ('a753037fefa24d348988e4ed417bd7f4','3bd3393a26474f81bd7615be4a656d94');",
		's8'=>"delete from user_item where uuid in ('25fbd77d768542ae90f837ab127e3dc1');",
		's81'=>"delete from user_item where uuid in ('673bd52352d147df8ef0210e6ba31016','e33bdfc4f5db40a4b4a258085f5c5a5c','6e55adc3cb2844faa0b3a5e06f47a3f8');",
		's900001'=>"delete from user_item where uuid in ('968d2f773bd7429094f8b636de091122','ddd75001f94448d2b34f9edb4f7ee059','651624e6afef4fc09e13eac848449b6f','f0420a72af004f119a5d40e24d32d2e3','93dc1583bb264e09b16b3085159cd269');",
		's900002'=>"delete from user_item where uuid in ('cce397b424074f84bcedc235ecf586f6','6030dd44743b481498ca70be0e23c7a7');",
		's900003'=>"delete from user_item where uuid in ('6fad2e0192b942b388a3e215c377677a','419928f9327240f98c1c4ebb4ad42dcf','770c2ea6b91b46198ce4edf8e104e960');",
		's900004'=>"delete from user_item where uuid in ('4dada9ba5c6b4245a784a7c72849bdc9','0bbb0b4c2c924aac88a8711f9e4e5f21','8c0638f969ad412db1e2e7a6f830aa54');",
		's900005'=>"delete from user_item where uuid in ('f9679e294f7a415b933f336f6c5a1526','eea75120e19d4202bdc680c7d3da90db','ccdd9b2e62f64b9f8c1cb26ebc3d4995','6ffda912185847928fb07ea3e57a2188','fdbeebb4974a43c989df4307b6143c22');",
		's900006'=>"delete from user_item where uuid in ('581224cabd9546529a09060c7b16fc2d');",
		's900008'=>"delete from user_item where uuid in ('1332022cc4c24731a33531616da802ce','1c2b3739cc904f9a9e07f7b6dfba2dad');",
		's900009'=>"delete from user_item where uuid in ('f3366ebf24264523b0d36fb14869b718','5ff6abb2e9ca453f933322ca348e05f6','08976b33cc3f4e7b9b74d5d93b579dbf');",
		's900012'=>"delete from user_item where uuid in ('bef3f25af4374826a6ddf437f6ba04e0','f53727df25b348ac97a80bc77cc51b2e');",
		's900014'=>"delete from user_item where uuid in ('9d2b7ed4cba54e7b9e21df8ae5576aec');",
		's900015'=>"delete from user_item where uuid in ('e4cfd31866f048cdb348a8d9621f33a8','141127ff1cd947f9b438c187756e864c','0a24fac0ea864348b838cba7f2eb0b6c');",
		's900016'=>"delete from user_item where uuid in ('09453d004e6d4240abbee28aad3b0582','edcc3d94c9da4b0b90bc11db75355c45','83a95301df8a48a4b3793e636052b33f','de8a6bbcb07f44288d17812872739a06');",
		's900017'=>"delete from user_item where uuid in ('c41d6c7841044bc6a6031ff49e3dab08','b3a30d64414f4a248d6591c04d6e9455','be0e7a20b785460c8805fe32b75731ab','94fa8f7a7e0a4f79995511471a3c56af');",
		's900018'=>"delete from user_item where uuid in ('32d9cf02289545c8923e90d70b0c8810');",
		's900019'=>"delete from user_item where uuid in ('511cf761b2f444b1bddc67b7154a67f9');",
		's900020'=>"delete from user_item where uuid in ('30c2379598ce429e8bedfb8cdf82db39','67753bf4a44a48ad9e19b3c3de45fe10');",
		's900021'=>"delete from user_item where uuid in ('4eb55f89fddd4edeb985ab52f028315a');",
		's900027'=>"delete from user_item where uuid in ('ba7cb3b092e843c288903e185fae35cd','a86b029519b04213a5af68394358d97b');",
		
);

$file='/data/log/zhengchen_delete_20150909.log';
foreach ($infoArray as $server=>$sql){
	$sid=substr($server, 1);
	$link = mysqli_connect($slave_db_list[$sid]['ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[$sid]['dbname'],$slave_db_list[$sid]['port']);
	if (mysqli_query($link, $sql)) {
		file_put_contents($file, $sid."Record updated successfully\n",FILE_APPEND);
	} else {
		file_put_contents($file,$sid."Error updating record: " . mysqli_error($link)."\n",FILE_APPEND);
	}
}


/*foreach ($slave_db_list as $idKey=>$DBvalue){
	$sid=$idKey;
    $link = mysqli_connect($slave_db_list[$sid]['ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[$sid]['dbname'],$slave_db_list[$sid]['port']);
    //$sql="insert into switches(name,stat) values('guide_skip_2',0);";
    $sql="UPDATE server_push SET updateVersion = '1.1.3' where uid = 'AppStore74a26772b2517e070d6d7c65f763f46d';";
    if (mysqli_query($link, $sql)) {
    		echo $sid."Record updated successfully\n";
    } else {
    		echo $sid."Error updating record: " . mysqli_error($link)."\n";
    }
    break;
}*/

