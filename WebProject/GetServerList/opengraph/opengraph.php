<?php
  $params = array_merge($_GET,$_POST);
  if (!isset($params['id'])) {
    exit("error id");
  }

  $id = $params['id'];
  unset($params['id']);

  if (isset($params['fb_locale'])) {
    $locale = $params["fb_locale"];
  }elseif (isset($_SERVER['HTTP_X_FACEBOOK_LOCALE'])) {
    $locale = $_SERVER['HTTP_X_FACEBOOK_LOCALE'];
  }else{
    $locale = "en_US";
  }

  $config_file = "./config/content.json";
  $dialog_file = "./config/dialog.json";
  if (!file_exists($config_file)) {
    exit("Config File Not Exist");
  }
  if (!file_exists($dialog_file)) {
    exit("Dialog File Not Exist");
  }

  $content = json_decode(file_get_contents($config_file), true);
  $dialog = json_decode(file_get_contents($dialog_file), ture);
  if (!is_array($content)) { exit("Content Format is not correct");}
  if (!is_array($dialog)) { exit("Dialog Format is not correct"); }

  $title = $content[$id]["title"];
  $description = $content[$id]["description"];
  
  $title_dialog = $dialog[$title][$locale];
  $description_dialog = $dialog[$description][$locale];
  $image = $content[$id]["image"];

  $cu = "cok://story/".$id;
  $url = "https://apps.facebook.com/clash-of-kings/";
  $objUrl = "http://coq.elex.com/opengraph/opengraph.php?id=".$id;
  $canvas_url = addParams($url,$params);
  $deep_linking = addParams($cu,$params);
  $canonicalURL = addParams($objUrl, $params);

  function addParams($url,$params){
    if (count($params) > 0) {
      if(strpos($url, "?") > 0){
        return $url."&".http_build_query($params);
      }else{
        return $url."?".http_build_query($params);
      }
    }else{
      return $url;
    }
    
  }

    $lang = array("zh_CN","zh_TW","en_US","ko_KR","th_TH","de_DE","ru_RU","pt_BR","ja_JP","fr_FR","es_ES","tr_TR","it_IT","nl_NL","pl_PL" );
?>
<html>
    <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# clash-of-kings: http://ogp.me/ns/fb/clash-of-kings#">
        <title>Clahs of Kings</title>
        <meta property="og:locale" content="<?php echo $locale;?>" />
        <?php 
        foreach ($lang as $key => $value) {
          if ($value != $locale) {
            echo '<meta property="og:locale:alternate" content="'.$value.'"/>';
            echo "\n\t";
          }
        }
        ?>
        <?php if ($content[$id]["wide"] != 1) { ?>
        <meta property="fb:app_id" content="713457855362204" />
        <?php } ?>
        <meta property="og:title" content="<?php echo sprintf($title_dialog,$params["tph1"],$params["tph2"]); ?>" />
        <meta property="og:description" content="<?php echo sprintf($description_dialog, $params["dph1"], $params["dph2"]); ?>" />
        <meta property="og:image" content="<?php echo $image;?>" />
        <meta property="og:url" content="<?php echo $canonicalURL;?>" />
        <?php if ($content[$id]["type"]) { ?>
<meta property="og:type" content="<?php echo $content[$id]["type"]; ?>" />
        <?php }?>

        <?php if ($content[$id]["game:points"]) { ?>
          <meta property="game:points" content="<?php echo $content[$id]["game:points"] ?>" />
        <?php }?>

        <?php if ($content[$id]["video"]) { ?>

        <!-- Video -->
        <meta property="og:video:url" content="<?php echo $content[$id]["video"]."?autoplay=1&rel=0&playsinline=1"; ?>">
        <meta property="og:video:secure_url" content="<?php echo $content[$id]["video"]."?autoplay=1&rel=0&playsinline=1"; ?>">
        <meta property="og:video:type" content="text/html">
        <meta property="og:video:width" content="1280">
        <meta property="og:video:height" content="720">
        <meta property="og:video:url" content="<?php echo $content[$id]["video"]."?autoplay=1&rel=0&playsinline=1&version=3&autohide=1"; ?>">
        <meta property="og:video:secure_url" content="<?php echo $content[$id]["video"]."?autoplay=1&rel=0&playsinline=1&version=3&autohide=1"; ?>">
        <meta property="og:video:type" content="application/x-shockwave-flash">
        <meta property="og:video:width" content="1280">
        <meta property="og:video:height" content="720">

        <?php }?>

        <!-- App Links -->
          <!-- Android -->
          <meta property="al:android:url" content="<?php echo $deep_linking; ?>" />
          <meta property="al:android:package" content="com.hcg.cok.gp" />
          <meta property="al:android:app_name" content="Clash of Kings" />
          <meta property="al:android:class" content="com.clash.of.kings.EmpireActivity" />
          <!-- iOS -->
          <meta property="al:ios:url" content="<?php echo $deep_linking; ?>" />
          <meta property="al:ios:app_name" content="Clash of Kings - Last Empire" />
          <meta property="al:ios:app_store_id" content="945274928" />
          <!-- Web -->
          <meta property="al:web:should_fallback" content="false" />
          <meta http-equiv="refresh" content="0;url=<?php echo $canvas_url;?>" />
    </head>
    <body>
      Redirecting...
    </body>
</html>
