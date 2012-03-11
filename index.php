<?php

    // includes
    include_once("includes/types.php");
    include_once("includes/functions.php");

    // lists
    $categories = array();
    $games = array();

    // load categories
    loadCategories($categories);

    // load games
    loadRetailGames($games);

    // parse achievements from Xbox.com, new format JSON
    parseAchievementsJson(file_get_contents("data/achievements.json"), $games);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title>XBOX360 games</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="author" content="lukasz.milewski@gmail.com" />
  <link rel="stylesheet" type="text/css" media="screen" href="styles.css" />
  <script src="jquery.js"></script>
  <script src="jquery.tinysort.js"></script>
</head>
<body>

  <div class="top">
    <span><strong>XBOX360</strong> games</span>
  </div>

  <div class="tabs">
    <div class="filters" id="notreleased">not released</div>
    <div class="filters" id="notowned">not owned</div>
    <div class="filters selected" id="notplayed">not played</div>
    <div class="filters selected" id="rented">rented</div>
<?php

    $selectedCategory = false;

    foreach ($categories as $category)
    {
?>
    <a id="<?= $category->id ?>"<?php if ($category->active) {?> class="active"<?php } ?>><?= $category->name ?></a>
<?php

    }

?>
  </div>

  <div class="main">

<?php

    foreach ($games as $group)
    {
        $i = 1;

?>    <ul id="content<?= $group->name ?>" class="content tiles<?= $group->name == $categories[0]->id ? " active" : "" ?>">
<?php

        foreach ($group->games as $game)
        {
            $addtnl = "";

            if ($game->renter != null)
            {
                $addtnl = " rented";
            }
            else if ($game->released != null && $game->released != "0")
            {
                $addtnl = " notreleased invisible";
            }
            else if ($game->released != null && $game->released == "0")
            {
                $addtnl = " notowned invisible";
            }
            else if ($game->achPoints->value == null || $game->achPoints->value == 0)
            {
                $addtnl = " notplayed";
            }
            else if ($game->achPoints->value == $game->achPoints->outOf)
            {
                $addtnl = " completed";
            }

            $achPointPercentage = 0;
            if ($game->achPoints->outOf > 0)
            {
                $achPointPercentage = floor($game->achPoints->value / $game->achPoints->outOf * 100);
            }

            $classColorPercentage = floor($achPointPercentage - ($achPointPercentage % 10));

            ob_start("newlines");

?>
      <li class="game<?= $addtnl ?>">
        <span class="id"><?= $i++ ?></span>
        <img src="<?= $game->getBoxArt() ?>" />
        <span class="name"><?= $game->getGameName() ?></span><br />
        <span class="achievements">
          <?= $game->achPoints->value ?>&nbsp;/&nbsp;<?= $game->achPoints->outOf ?><br />
          <span class="progress"><span class="count p<?= $classColorPercentage ?>" style="width: <?= $achPointPercentage ?>%;"><?= $achPointPercentage ?></span></span>
        </span>
<?php

            if ($game->renter != "")
            {

?>
          <span class="renter"><?= strtolower($game->renter) ?></span>
<?php

            }
?>
<?php

            if ($game->released != null && $game->released != "0")
            {

                $released = parseDate($game->released);

?>
        <span class="sep"></span>
        <span class="release"><?= $released ?></span>
<?php

            }

?>
      </li>
<?php

      ob_end_flush();

  }

?>
    </ul>

<?php } ?>
  <div style="clear: both;"></div></div>

  <div class="footer">
    Last modified: <?= date("F d Y H:i:s", filemtime("data/achievements.json")) ?><br />
    <button id="sOaz">SORT Original a-z</button>
    <button id="sOza">SORT Original z-a</button>
    <button id="sNaz">SORT Name a-z</button>
    <button id="sNza">SORT Name z-a</button>
    <button id="sPaz">SORT Completion 0%-100%</button>
    <button id="sPza">SORT Completion 100%-0%</button>
  </div>

  <script>

    var currentTab = '<?= $categories[0]->id ?>';

    function changeClass(tab)
    {
        $('#content' + currentTab).removeClass('active');
        $('#' + currentTab).removeClass('active');

        $('#content' + tab).addClass('active');
        $('#' + tab).addClass('active');

        currentTab = tab;
    }

    function showHide(type)
    {
        $('li').each(function(index, li)
        {
            if ($(li).hasClass(type))
            {
                $(li).toggleClass('invisible');
            }
        });

        $('#' + type).toggleClass('selected');
    }

<?php

    foreach ($categories as $category)
    {

?>
    $('#<?= $category->id ?>').click(function(){changeClass('<?= $category->id ?>')});
<?php

    }

?>

    $('#completed').click(function(){showHide('completed')});
    $('#rented').click(function(){showHide('rented')});
    $('#notowned').click(function(){showHide('notowned')});
    $('#notreleased').click(function(){showHide('notreleased')});
    $('#notplayed').click(function(){showHide('notplayed')});

    $('#sOaz').click(function(){$('ul#content' + currentTab + '>li').tsort('span.id')});
    $('#sOza').click(function(){$('ul#content' + currentTab + '>li').tsort('span.id', {order:"desc"})});
    $('#sNaz').click(function(){$('ul#content' + currentTab + '>li').tsort('span.name')});
    $('#sNza').click(function(){$('ul#content' + currentTab + '>li').tsort('span.name', {order:"desc"})});
    $('#sPaz').click(function(){$('ul#content' + currentTab + '>li').tsort('span.id');$('ul#content' + currentTab + '>li').tsort('span.count')});
    $('#sPza').click(function(){$('ul#content' + currentTab + '>li').tsort('span.id');$('ul#content' + currentTab + '>li').tsort('span.count', {order:"desc"})});

  </script>

</body>

