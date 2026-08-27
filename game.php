<?php

require 'db.php';

/* =========================
   AJAX ПАРАМЕТРЫ
========================= */

$search = $_POST['search'] ?? '';
$genre = $_POST['genre'] ?? '';
$people = $_POST['people'] ?? '';
$platform = $_POST['platform'] ?? '';
$sort = $_POST['sort'] ?? 'new';

/* =========================
   SQL
========================= */

$sql = "SELECT * FROM games WHERE 1=1";

if ($search !== '') {
    $search = mysqli_real_escape_string($link, $search);
    $sql .= " AND name LIKE '%$search%'";
}

/* жанры (через запятую) */
if ($genre !== '') {
    $genre = mysqli_real_escape_string($link, $genre);
    $sql .= " AND genre LIKE '%$genre%'";
}

if ($people !== '') {
    $people = (int)$people;
    $sql .= " AND people = $people";
}

if ($platform !== '') {
    $platform = mysqli_real_escape_string($link, $platform);
    $sql .= " AND platform='$platform'";
}

/* =========================
   СОРТИРОВКА (STEAM STYLE)
========================= */

switch ($sort) {

    case 'popular':
        // если нет popularity — используем id как базовую популярность
        $sql .= " ORDER BY id DESC";
        break;

    case 'new':
    default:
        $sql .= " ORDER BY id DESC";
        break;
}

/* =========================
   EXEC QUERY
========================= */

$result = mysqli_query($link, $sql);

/* =========================
   AJAX РЕЖИМ
========================= */

if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {

    while ($row = mysqli_fetch_assoc($result)) {

        echo '<div class="game">';

        echo '<div class="game_image">';
        echo '<img style="width:100%;" src="/img/'.$row['img'].'">';
        echo '</div>';

        echo '<div class="game_desk">';

        echo '<div class="desk_top">';
        echo '<div><h2 style="font-size:30px; font-weight:500; font-family: Tektur, sans-serif;">'
            .htmlspecialchars($row['name']).
        '</h2></div>';

        echo '<div class="person">'.$row['people'].'</div>';
        echo '</div>';

        echo '<div class="genre">'.htmlspecialchars($row['genre']).'</br>'.htmlspecialchars($row['platform']).'</div>';

        echo '<div class="game_title">'.htmlspecialchars($row['title']).'</div>';

        echo '</div>';
        echo '</div>';
    }

    exit;
}

/* =========================
   HEADER + ФИЛЬТРЫ ДАННЫЕ
========================= */
?>
<div class="hero-bg-game"></div>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/header.html");

/* жанры из CSV */
$genresRaw = mysqli_query($link, "SELECT genre FROM games");

$genresSet = [];

while ($row = mysqli_fetch_assoc($genresRaw)) {
    $parts = explode(',', $row['genre']);

    foreach ($parts as $g) {
        $g = trim($g);
        if ($g !== '') {
            $genresSet[$g] = true;
        }
    }
}

$genresList = array_keys($genresSet);
sort($genresList);

/* платформы */
$platformsResult = mysqli_query($link, "SELECT DISTINCT platform FROM games ORDER BY platform");
?>



<script>
document.addEventListener("DOMContentLoaded", () => {
  const isHome = window.location.pathname === "/" || window.location.pathname === "/index.html";

  document.querySelectorAll('a[href="#price"]').forEach(a => {
    a.href = isHome ? "#price" : "/#price";
  });

  document.querySelectorAll('a[href="#about"]').forEach(a => {
    a.href = isHome ? "#about" : "/#about";
  });
});
</script>




<main class="container">

<div class="club">

<div class="catalog-header">
    <h1>Каталог игр</h1>

    <p>
        От динамичных VR-шутеров до уютных кооперативных приключений —
        выбирайте игру по настроению и собирайте свою команду.
    </p>
   
<div class="catalog-icons">


    <div class="catalog-icon">
        <div class="catalog-icon-img">
            <img src="img/console (1).png" alt="">
        </div>
        <div class="catalog-icon-text">
            <h5>50+ игр</h5>
             <p>Постоянно пополняем коллекцию</p>
        </div>
    </div>

    <div class="catalog-icon">
         <div class="catalog-icon-img">
            <img src="img/vr (1).png" alt="">
        </div>
        <div class="catalog-icon-text">
            <h5>VR и Playstation</h5>
             <p>Лучшие игры для любых предпочтений</p>
        </div>
    </div>


    <div class="catalog-icon">
         <div class="catalog-icon-img">
            <img src="img/steering-wheel (1).png" alt="">
        </div>
        <div class="catalog-icon-text">
            <h5>Сим-рейсинг</h5>
             <p>Симуляторы гонок с профессиональным рулем</p>
        </div>
    </div>


    <div class="catalog-icon">
         <div class="catalog-icon-img">
            <img src="img/family (1).png" alt="">
        </div>
        <div class="catalog-icon-text">
            <h5>Для Всех!</h5>
             <p>Подходит для детей и взрослых</p>
        </div>
    </div>


</div>
<!-- =========================
     ФИЛЬТРЫ
========================= -->

<form id="filters" class="filters" style="z-index: 999; position: relative;">

    <!-- SEARCH -->
    <input type="text" name="search" placeholder="Поиск игры...">

    <!-- GENRE -->
    <select name="genre">
        <option value="">Все жанры</option>

        <?php foreach ($genresList as $g): ?>
            <option value="<?= htmlspecialchars($g) ?>">
                <?= htmlspecialchars($g) ?>
            </option>
        <?php endforeach; ?>

    </select>

    <!-- PEOPLE -->
    <select name="people">
        <option value="">Любое количество игроков</option>
        <option value="1">1 игрок</option>
        <option value="2">до 2 игроков</option>
    </select>

    <!-- PLATFORM -->
    <select name="platform">
        <option value="">Все платформы</option>

        <?php while ($p = mysqli_fetch_assoc($platformsResult)): ?>
            <option value="<?= htmlspecialchars($p['platform']) ?>">
                <?= strtoupper($p['platform']) ?>
            </option>
        <?php endwhile; ?>

    </select>

    <!-- SORT (STEAM STYLE) -->
  <button type="button" id="resetFilters" class="reset-filters">
    Сбросить фильтры
</button>

</form>
 </div>
<!-- =========================
     СПИСОК
========================= -->

<div id="game_list_all" class="game_list_all">

    <div class="title">
        <h2>Список игр</h2>
    </div>

    <hr>

    <div id="games_container" class="games">

        <?php while ($row = mysqli_fetch_assoc($result)): ?>

            <div class="game">

                <div class="game_image">
                    <img style="width:100%;" src="/img/<?= $row['img'] ?>">
                </div>

                <div class="game_desk">

                    <div class="desk_top">
                        <div>
                            <h2 style="font-size:30px; font-weight:500; font-family: Tektur, sans-serif;">
                                <?= htmlspecialchars($row['name']) ?>
                            </h2>
                        </div>

                        <div class="person">
                            <?= (int)$row['people'] ?>
                        </div>
                    </div>

                    <div class="genre">
                        <?= htmlspecialchars($row['genre']) ?>
                    </div>

                    <div class="game_title">
                        <?= htmlspecialchars($row['title']) ?>
                    </div>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

</div>

</div>
<div class="catalog-about">
    <p>Иногда хочется просто отвлечься от повседневных дел, собрать друзей и провести несколько часов в хорошем настроении. </p>
        <p>Для этого мы и создали наш клуб. Здесь можно погрузиться в виртуальную реальность, устроить дружеский турнир на PlayStation, 
        попробовать необычные кооперативные игры или открыть для себя что-то совершенно новое.</p>

<p>В этом разделе собраны игры, доступные в клубе прямо сейчас. Изучайте каталог, выбирайте жанры, которые вам нравятся, и приходите за эмоциями, 
    которые невозможно получить дома в одиночку. Самые яркие моменты всегда начинаются с хорошей компании и интересной игры.</p>
<div class="links">
          <a class="link" href="tel:+79377350700">Позвонить</a>
          <a class="link" href="https://vk.com/vrdropvlz?w=app6013442_-220448469%2523form_id%253D1">Забронировать в ВК</a>
</div>
</div>

<!-- =========================
     JS
========================= -->

<script>

document.getElementById('link-about').href = '/#about';
document.getElementById('link-price').href = '/#price';


const form = document.getElementById('filters');
const container = document.getElementById('games_container');

const resetBtn = document.getElementById('resetFilters');
resetBtn.addEventListener('click', () => {

    form.reset();

    loadGames();

});


function loadGames() {

    const formData = new FormData(form);
    formData.set('ajax', '1');

    fetch(window.location.pathname, {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(html => {
        container.innerHTML = html;
    });
}

form.addEventListener('input', loadGames);
form.addEventListener('change', loadGames);


loadGames();

</script>

        <div id="contact">





        <?php


require_once($_SERVER['DOCUMENT_ROOT'] . "/footer.html"); 
?>
</div>