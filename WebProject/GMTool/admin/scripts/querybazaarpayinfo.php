<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');
$orderList = array(
'_fkF3S9cS0fuUTNT,gold_8'
,'-8VCwvHPLwZMQzzN,gold_104'
,'-9uOpSvNoPZ7xMf_,gold_8'
,'0VQ_ru0fnmpxo2DV,gold_1'
,'10429889198273,gold_8'
,'13859863302771,gold_8'
,'14622682585560,gold_3'
,'149040682759,gold_1'
,'15071755529225,gold_8'
,'15255505818524,gold_104'
,'16508742572517,gold_1'
,'17130742219808,gold_104'
,'19196663919670,gold_8'
,'20282004061667,gold_1'
,'209019088849,gold_8'
,'21110301731025,gold_1'
,'23353998127642,gold_8'
,'24814068608768,gold_8'
,'26181734023535,gold_8'
,'26329642252203,gold_8'
,'26444403478970,gold_1'
,'26785774951635,gold_8'
,'27023083157266,gold_8'
,'28763970838455,gold_8'
,'29921671071899,gold_8'
,'2bUuMKFzic_zjn73,gold_8'
,'2xtYSHqQy8qErt04,gold_8'
,'31221923775500,gold_3'
,'34274788524438,gold_2'
,'34429142456818,gold_8'
,'34616969166079,gold_1'
,'36343178040170,gold_8'
,'36586343797514,gold_8'
,'37773378562294,gold_1'
,'3885220728043,gold_8'
,'38997512126260,gold_8'
,'39616771696703,gold_8'
,'39987479211789,gold_1'
,'40557365209684,gold_8'
,'421R-edqpwTlMLwW,gold_8'
,'42466739437759,gold_8'
,'44bkukNkBrj3-4nk,gold_106'
,'46690172691613,gold_8'
,'47558257029045,gold_8'
,'48217699530558,gold_8'
,'48719568093919,gold_8'
,'48812162563262,gold_2'
,'4cavnm0EEKqD3RBA,gold_8'
,'50095798537257,gold_8'
,'50123127310508,gold_8'
,'50637547530169,gold_8'
,'5263727961204,gold_2'
,'52667286530767,gold_8'
,'53165537555309,gold_1'
,'53YRtoL42lL2quy0,gold_8'
,'55647689094483,gold_1'
,'55798915640492,gold_8'
,'55803980563781,gold_1'
,'57194852640575,gold_1'
,'58974784396966,gold_1'
,'59556450558119,gold_1'
,'6-S7GCuAqjIaCpVw,gold_8'
,'61057412835263,gold_1'
,'62363431148254,gold_8'
,'62932433307253,gold_8'
,'64497532000594,gold_8'
,'6504286107380,gold_8'
,'66446717857076,gold_1'
,'66708140238941,gold_8'
,'68038541066651,gold_8'
,'6858062930143,gold_8'
,'68760540005940,gold_8'
,'69164196248915,gold_1'
,'69408821638423,gold_8'
,'6973865512111,gold_8'
,'6YXLzK515Hxtd6XU,gold_2'
,'79UZWJ15fM9mIM9B,gold_8'
,'8DiALFidKAOVTjXO,gold_8'
,'91-knDWGcTMyKRL6,gold_8'
,'91A6YiguHbjnq-rC,gold_104'
,'9rK8u37qFbt5xkg3,gold_8'
,'AeC-rSI03E7IyQaO,gold_1'
,'aJzwZ9otvuVhjvVa,gold_8'
,'aPcLZ17UkORHYlHC,gold_1'
,'aytpIUtYR1jkuQVt,gold_8'
,'bqPsV01c-ultpHi9,gold_8'
,'cIzQSB1T6x2NqNsw,gold_8'
,'CXtocB5k0UH_RuM1,gold_1'
,'DCJuBhnTS3mJjvXS,gold_8'
,'DdkC51jMZF3jahke,gold_8'
,'difp3GQ4kpZQ0vO6,gold_8'
,'dq0KooPCHal3q8t3,gold_3'
,'DQ2hQy-XTr_zcy3e,gold_8'
,'eH3O7328GOPwbkK7,gold_2'
,'Ek9L_bIxQmKTyTl7,gold_8'
,'eNMjcTbCYX2Hhre3,gold_104'
,'EU6DlH5wSKcNR-Bl,gold_6'
,'F5zfHH-qXeG6QGrT,gold_2'
,'FLCu4OI_E5nJVNnB,gold_8'
,'GCrhq6IfXyptHoW_,gold_104'
,'gqA00_g9FtgoS7S1,gold_8'
,'gx0pDXfa5kt69lhR,gold_1'
,'htmBKlnDATzsnqus,gold_8'
,'HTs9a3CAKLFATzYY,gold_1'
,'iS6AJoltgiBjNyXn,gold_8'
,'Iw_Q1fD-Yd1I4ISX,gold_8'
,'iZQbTQn799QbPA9a,gold_104'
,'j9frUdQ0FJpfEPsR,gold_1'
,'kCH9vw8Xk5TKeqHP,gold_8'
,'kTGgOY9n5qC-KY7o,gold_1'
,'LCq5EVnAbuq0WbW9,gold_8'
,'lZ0OouBIjQjLzr9J,gold_1'
,'m7br5lx0cUu6aNG8,gold_8'
,'M98ZLgfK5ddzxpFf,gold_6'
,'m9oo4gLgsMHMDfqY,gold_2'
,'MCDKv6qPAmiKckaz,gold_8'
,'mFbqtPk0nmm7eBlE,gold_8'
,'Mp_cjrZVwKznjXsA,gold_6'
,'MvkBSlGupILOwAm9,gold_1'
,'MxlhBzmTWdb_yGVA,gold_8'
,'NklNGm1eidYEHwQk,gold_8'
,'nyIk-rHvkZQdcrZL,gold_4'
,'oa8iUFJG13-0OIN7,gold_8'
,'Os9G7TMPwCLo2_1L,gold_8'
,'P_CYwTd3swM4kkPjk,gold_2'
,'P_w5X-N84WEEwivFQ,gold_8'
,'P-_JOdn9Idm6lbLZd,gold_1'
,'P-TWqMABhWN_83feB,gold_103'
,'P0FKrq9NvScK6kIgY,gold_8'
,'P0SizkfP-9xf2kAHU,gold_8'
,'P16Gf0PR-Dl2j50ug,gold_8'
,'P18Ea8Za97rcwLw-_,gold_102'
,'P18z69A0yNxVPgXh-,gold_8'
,'P4vjHVXbk1TV8keCi,gold_1'
,'P5JJr-LbBGB5rRj_n,gold_8'
,'P5lq5Q6xQHAVHPvoH,gold_8'
,'P5P7ojFKs98hKEtqV,gold_1'
,'P6NoHbtiStZ0a-dCr,gold_8'
,'P6WVgobg6CHvtkotX,gold_8'
,'P6x8MxCoP7MA_Z3_h,gold_8'
,'P7aznN4-0z2jcOmct,gold_1'
,'P88YOgSz1OwvOWwHK,gold_3'
,'P8C_5FKPu4bGnPll1,gold_8'
,'P8FiNl1_-P7Sc5w5M,gold_8'
,'P8s7bkJn89zNP5fMd,gold_8'
,'P9JrTVKMxPERrRyVZ,gold_8'
,'PAfyZMiq9zTD42SIU,gold_8'
,'Pb9aKKv6pQ9WJIqe1,gold_1'
,'Pbdi5sRYakQS_mh8x,gold_8'
,'Pbq0g2L0TKc7_zrF8,gold_1'
,'PbzKSYXrhSu2h6PbW,gold_1'
,'Pc-T0xGGhjoqZ9R8r,gold_8'
,'PciptvcBprCYwwcR2,gold_2'
,'PcYClilUmsHuLswiC,gold_1'
,'PdRdUYNS90oqQX7S1,gold_1'
,'PdTCEsrcwcBjD9ubL,gold_8'
,'PeHBiQsjRvxGSePJV,gold_8'
,'Pet1ALtQe-Ue6dS74,gold_8'
,'Pf0fVlqqs22lBrQRG,gold_2'
,'PfpX5aVry21iaDEfT,gold_8'
,'PFVh0MQgT914ipGHS,gold_1'
,'PG_l2D9-nXbXag-13,gold_8'
,'PgkMIRpGESZPJaLpB,gold_8'
,'PgUu0gwcXNSUEDEzB,gold_8'
,'PGW4nItYwzsEoIUSS,gold_103'
,'PGyFM8oFvmHhXgqrQ,gold_8'
,'Ph6PxVcIBCByE-dcN,gold_1'
,'PhEZVoeLWz8npQsF0,gold_8'
,'PHiqe_dHYL4yTt1mK,gold_101'
,'PhtTQMhqhRu-PDJOC,gold_102'
,'PHWvgTYWCKuLgweFv,gold_104'
,'Pi_BnEdjIh06ta8wY,gold_2'
,'PI-zTNcHEU2ETsAN5,gold_8'
,'PI60SUKw2zOFQDVzu,gold_8'
,'PiGkbxeo2brILoqfY,gold_1'
,'PigR9RDk5rPduvRKu,gold_8'
,'PiziiixJZ2doRWmIe,gold_8'
,'PIZrpbevJI95l_zRo,gold_8'
,'PJ24SBI7q8nlQmeaS,gold_8'
,'PJpvYD-oJuDuY70Iq,gold_8'
,'PJWZurrjxjaJifEK3,gold_1'
,'PK0vI1Cin1rUf_lwK,gold_8'
,'PKBV0fAHl62wvcFCY,gold_1'
,'PKCASdKf-2ucuaFcn,gold_1'
,'PKOxhK8VuP3L723HJ,gold_8'
,'PkYXNz9HQVQ_YHFdk,gold_1'
,'PlRNYNlLOxe9Pe6ly,gold_2'
,'PMf9bt1eNRqcAfdsl,gold_8'
,'PmfLTBUMF8ZoNX1d1,gold_2'
,'PMGRGL51JcBzdW3wK,gold_2'
,'PMktGkybCXqJ2WP9r,gold_2'
,'PmpuU9P_R5X6d12_W,gold_8'
,'PmzlauPixBpa9djrs,gold_1'
,'PNbH3WNed0kJ8GzuG,gold_8'
,'PnjOR1OjHTDdAYh1N,gold_8'
,'PNkMNcFqEb2rnaE91,gold_1'
,'PNTr3BKXIWU5-Xug8,gold_8'
,'PO4LT0A0luJ5mRbo_,gold_3'
,'POkSlJ49KKSN9wNyS,gold_2'
,'POQPIAkW6lUA8kibZ,gold_1'
,'Pp9HUBPH30ml91oPN,gold_6'
,'PPdCnvpyyVIw3QPCL,gold_8'
,'PPGd-Z1VDFqy2Oshn,gold_101'
,'PPnL5GG4zVdGU_q-u,gold_2'
,'PQ0UlWCpXwNkFCU1F,gold_8'
,'PQ26zawPPPXI-nukM,gold_8'
,'PQ5NzvOII323P51LS,gold_8'
,'PQTwz1ctbWtMOMzEq,gold_3'
,'PQyaqFFqxIMOmYcGh,gold_8'
,'PRGS-0zBPj81F_2nv,gold_8'
,'ps-lZwnT4GLGE50_,gold_8'
,'Psel1Vh2Xd88HjUub,gold_8'
,'PSJ0N9Jy91s3RIHJz,gold_1'
,'Psn9acYKhZYfgWXXY,gold_8'
,'PSQQ_ywhIWvcwZ8Xm,gold_1'
,'PTaR1ghgbzNVsP0ZS,gold_8'
,'PtCFensDwPpTRp8Ez,gold_8'
,'PtjfthuhMpMrpfEnA,gold_8'
,'PTQG6UC0b_dtzIm3Y,gold_8'
,'PttEkVgNzFYxZXLc6,gold_2'
,'PUevy-otQHZLaJx35,gold_8'
,'Puz7PBGgohS4OdOO6,gold_8'
,'PvHscSvfuEVHO0Gdo,gold_1'
,'PVicEPGkXC7yY2BEG,gold_2'
,'PvQyIHpaEDIBgRhgk,gold_8'
,'Pw408nofg2PCWwc6a,gold_6'
,'PwgejGWtNQGILoRpJ,gold_1'
,'PwJfvBc3htQSA600h,gold_8'
,'PwP8bubj53L4wkGax,gold_8'
,'Px8TKlVW9fpAcMokS,gold_1'
,'Pxv97cIb6z9SsalZH,gold_8'
,'PY-gBy73yX1EzAZtf,gold_1'
,'PYjzHlRhI9WEIE-gX,gold_8'
,'PYQpTv8ahyg7F21xu,gold_8'
,'PYTTtjXHwYwdIHDlZ,gold_8'
,'PYYTy3DO5V0MuiW1B,gold_1'
,'q5ZhkveCGYVwFnaq,gold_8'
,'qcqk6sXVttcWPTIZ,gold_1'
,'qHr0fdcwzmQgsYLa,gold_8'
,'QuMWxJNlAU4LccSY,gold_1'
,'QwgGz_LCpqTbOl4I,gold_8'
,'RENFqzbtFVqXkwxS,gold_1'
,'RskPPJXdfNEungPx,gold_1'
,'RxDzeQrnLxK9Ormv,gold_3'
,'rZMjtUetmC5K40SR,gold_8'
,'S__PUAYcORkc-vx7u,gold_8'
,'S_-BgaK44vYdwLJgm,gold_1'
,'S0cTnXWOlxifWnHh-,gold_8'
,'S0hiA4LzTZmVfDQsk,gold_1'
,'S1GoMU41hsYSWIB8L,gold_8'
,'S1wZjAjwF2ZsMuh4F,gold_1'
,'S3HLox8wuJjVyLgkt,gold_1'
,'s3zbK16LyCa0mRFR,gold_8'
,'S49SabNKIU5_YPUp2,gold_8'
,'S4HEzNNL9_qU96rOo,gold_8'
,'S4SKuYxuHvLC8Dkfb,gold_8'
,'S4T6un4OEos7_K9D-,gold_8'
,'S4w27K4y7o6Vp3D70,gold_1'
,'S5WhtU5VRQzeeSXq-,gold_8'
,'S6kqWiFjjGFuMmDia,gold_8'
,'S6Y_PCLJy3ttJp4k5,gold_1'
,'S79nWWGso3SDJ7NvB,gold_1'
,'S7cYeu5WAK095UIJO,gold_8'
,'S8aH2pcUR91FDAkyj,gold_2'
,'S8sGcKJf5qWfOaRj4,gold_1'
,'S9-Lqp0zGLhggQ0n9,gold_4'
,'S9HBvLLZVfRZwebzW,gold_102'
,'SajKC7oVWnTeaDYdL,gold_8'
,'SAP6tEbgzFYCXh9Hd,gold_8'
,'SB7w7NLcoiTKhyV6i,gold_1'
,'SB9dIYElsocGBs2Lb,gold_1'
,'SbLXVyMvI3a1bPgNk,gold_3'
,'SBmY85q58RcFsadui,gold_6'
,'SBNH8AxClmSGwdDgy,gold_1'
,'SBS7cKGj5rljTu5Nv,gold_1'
,'SbThqK21nuf69Eon9,gold_1'
,'ScHp9EIGuxMme1asK,gold_1'
,'SCiBkS_YTom0iAIpA,gold_8'
,'SCIw_Z7k66bNI16_y,gold_1'
,'SCszoYsU0ZiDTS0Qk,gold_1'
,'SDKGN61AfUyhVMReT,gold_8'
,'SE6ZWDwZsa7Nsn4An,gold_8'
,'SEVoY6HFcyYTferAb,gold_1'
,'Sf0HJOQgEZtIV39zp,gold_1'
,'SF3wR2tMqf-jPBFXf,gold_1'
,'SFaLPDrEFJP57Z0NX,gold_8'
,'SfmdO7YsUprXzWaal,gold_106'
,'SfpO-99CplErBCc4-,gold_8'
,'SfUd007RRVlwi0Jdy,gold_8'
,'Sg_912fVunl8XvGK-,gold_1'
,'Sg4DIY_LtzADVp6F0,gold_1'
,'SgA7zxcFqadln47_H,gold_8'
,'Sgjwb0a0sA4FHsa0A,gold_1'
,'Sh2rCnACpeUQShY6H,gold_8'
,'Sh58YsOZjPaNP_LoW,gold_8'
,'SHC_8gQO7z75_a4ax,gold_8'
,'SHXBI4CenDII3DoFf,gold_8'
,'Si9eTBl5wMxkZCUra,gold_8'
,'SIoEjC-AzVFfW39xD,gold_8'
,'SIRydFveQuCLtwBVh,gold_8'
,'SjFTpfevIjt5Xikcw,gold_8'
,'SjHpq2TsVyxMkHRct,gold_101'
,'Sk-oAV6bYjxuzzNQM,gold_8'
,'Sk86datep_a9HPGvN,gold_8'
,'SkFUd6lzKc-_SJVxU,gold_1'
,'SKi5lTFAf2U4j3uEP,gold_104'
,'SKogCyXlVIgNBLwOP,gold_1'
,'SKrGLTL1Ey-SpXXNT,gold_8'
,'SlchEJNCRlzNk8K1_,gold_8'
,'SLtVR54qFZm6xNJHJ,gold_3'
,'SLy-mwJoPph8GceZ0,gold_8'
,'SLYt583ixwYg2l230,gold_8'
,'SlZ3dqry9_fy5Basq,gold_1'
,'SlZNd8wiP11aUQAyO,gold_4'
,'SMiTRRczKawOmbCH_,gold_104'
,'SMMZAoYozMNKsx5wk,gold_1'
,'Smqy6mM3b78CzJW1G,gold_8'
,'SMuc66pRzZ3g37_YV,gold_1'
,'SmuIuX2L9zLg230mL,gold_106'
,'SN_LklBXvgZ1C8zwL,gold_1'
,'SnitMm2KnoZ6VlcFX,gold_1'
,'SnmoDHU0P-M2vepKw,gold_2'
,'So8lTyMUpM8rcXvze,gold_8'
,'Sob2MUW7rWXyUFpgM,gold_1'
,'SoD-aB1GIvbQkFWrc,gold_1'
,'Sospk79GSaXHH96z5,gold_2'
,'SOu8PNWajxB_dpLnG,gold_8'
,'SoVESjPSYC_qAXO5f,gold_8'
,'SP7HYUmW6YDh-bz8a,gold_8'
,'SPcyJ4sVb0fgX3hJp,gold_104'
,'SpLdpD8Esvxa84T9G,gold_3'
,'Spsnn4dBMwz4t7YNx,gold_102'
,'SpwVQhowtbWI-vWjs,gold_8'
,'SqHpcFehoDSmx8X4O,gold_3'
,'SR8w9kITRGmCS_SCF,gold_2'
,'SRsnMpHesU9VGQqq_,gold_1'
,'Ss3WB6ZgJ7ZI56xV_,gold_104'
,'SSg7-pI6UXQNMJHDJ,gold_8'
,'SsH47YRA6TTlldNxm,gold_3'
,'SsHMCezw5j2cOTpQ5,gold_104'
,'SSKL4WJ-TBAvfYoKf,gold_1'
,'SsM07hxWNUQZcdI7U,gold_8'
,'STFGUH6vhslVqJaxX,gold_8'
,'Stqos1ODYA7Tv6XOd,gold_8'
,'StXXHtTispOcDNNJp,gold_8'
,'Su43akZGyHPi4OcRz,gold_1'
,'SuhSxQAr10VoAQGxk,gold_8'
,'SumRWM5PZ1X_uYQ88,gold_1'
,'SuRmjl_O1HlUyU1UN,gold_8'
,'SuVAQEbv2aJDlALDj,gold_4'
,'SUY8xTr02RjFHB1WQ,gold_2'
,'Sv0VpZC22qnNJup8Y,gold_8'
,'SV4Aa2CwQWt1xvWpb,gold_1'
,'SVcgYoSg0t2ubbXRN,gold_1'
,'SVGtuGPPmvxc_Ryea,gold_106'
,'Sw0F1U7bbXaj3VuC4,gold_8'
,'SW4S_3AXu-ylyq_K3,gold_8'
,'SWfblwRcL4oZt4EHM,gold_1'
,'SWMT9lpiymve9eQTP,gold_8'
,'SWTvF5vNHqMpZDmtK,gold_8'
,'SwwQ9SDnHKC3E5SEs,gold_8'
,'SXcit-xiRDeh68K6z,gold_5'
,'SXCsh26Wfqw_beOWr,gold_8'
,'SY_9sS6TsCl48XZRU,gold_1'
,'SY5bWeiKr-gk4ULtN,gold_8'
,'SyiOFJx2VrMMunEfn,gold_5'
,'SYpOOs-5QwY8cZ6o4,gold_1'
,'SyyOaeCRJEOumiTGF,gold_5'
,'SYYTXz6qlUJWEwSIF,gold_8'
,'Sz50GtjNv45z4P7Ry,gold_8'
,'tIu6jfVTXbxm2Kum,gold_105'
,'TJBVxv2JGnAtYUlc,gold_6'
,'tup9Jb2ZhaVtioSy,gold_1'
,'TWUtFXpYe0E75XV4,gold_8'
,'uf_LVRI7b-qdNGRr,gold_1'
,'v6Zv3UIwhwx8REuH,gold_8'
,'VhN7rOYE3quDpAK-,gold_1'
,'vQBtlJpvaV8Zdl3j,gold_6'
,'VTHFZUMSUoa0Bw7y,gold_1'
,'VZ0cSxlbRIOa1qHn,gold_8'
,'W666uItpdZad3KfX,gold_2'
,'WbxcO5i1cklcGf4F,gold_8'
,'wjyoct6igCZMvP9o,gold_8'
,'wL7ExvZRj7AjS6i2,gold_8'
,'WUHlvOyKf_xU-yfK,gold_1'
,'XlutRGaDvvDbKD99,gold_8'
,'Xojg_3j8YrXXWfXb,gold_8'
,'xV1SGVw7KnqM9jkj,gold_8'
,'xzsf1fyJsmOmQ5fR,gold_8'
,'YyAyhDDot7y4DRXW,gold_8'
,'yzvna1Jh2n1rcERJ,gold_8'
,'z13Df6Wiv-Cm66Cw,gold_1'
,'ZBRwR1nawGrnALyf,gold_2'
,'ZpSpLprGaZqHPSol,gold_8'
,'ZVzRXUFhoFbsI63a,gold_1'
);
$access_token = 'jVy1C8yGIYGuyrt5KakuQ1agNjclcU';
foreach ($orderList as $order){
	list($orderId,$sku) = explode(',',$order);
	$url = "https://pardakht.cafebazaar.ir/api/validate/com.hcg.cok.cafebazaar/inapp/$sku/purchases/$orderId/?access_token=$access_token";
	$result = callApi($url);
	echo $result."\n";
	$paydata = json_decode($result,true);
	$row = "$orderId $sku ".$paydata['developerPayload'];
	file_put_contents( ADMIN_ROOT .'/cafebazaarpayinfo.txt', $row . "\n",FILE_APPEND);
	echo $row."\n";
}

function callApi($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	if (is_array($headers) && count($headers) > 0)
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	if ($curlopt_header)
			curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch,CURLOPT_ENCODING, "gzip,deflate");
	curl_setopt($ch, CURLOPT_POST, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if (strpos($url, 'https') === 0) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	}
	$result = curl_exec($ch);
	return $result;
}
	
	
	function postUrl($url, $params,
		 $contentType = null, $body = null) {
		$headers = array(
				"Authorization: Basic "
						. base64_encode("{$this->accountKey}:{$this->accountSecret}")
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		if (is_array($headers) && count($headers) > 0)
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($curlopt_header)
			curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch,CURLOPT_ENCODING, "gzip,deflate");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if (is_array($params) && count($params) > 0) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($params));
		}
		if (strpos($url, 'https') === 0) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		$result = curl_exec($ch);
		return json_decode($result);
	}	
?>
