<?php
$tmdb_api_key = '4cacbd0368af2782da7a7aedf0e25cd3';
$movie_details = null;
$trailer_url = null;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $query = urlencode($_GET['search']);
    $search_url = "https://api.themoviedb.org/3/search/movie?api_key=$tmdb_api_key&query=$query&language=fr-FR";
    $search_response = file_get_contents($search_url);
    $search_data = json_decode($search_response, true);
    $first_result = $search_data['results'][0] ?? null;

    if ($first_result) {
        $movie_id = $first_result['id'];
        $details_url = "https://api.themoviedb.org/3/movie/$movie_id?api_key=$tmdb_api_key&language=fr-FR&append_to_response=videos,credits";
        $details_response = file_get_contents($details_url);
        $movie_details = json_decode($details_response, true);

        // Get trailer
        foreach ($movie_details['videos']['results'] as $video) {
            if ($video['site'] === 'YouTube' && $video['type'] === 'Trailer') {
                $trailer_url = 'https://www.youtube.com/embed/' . $video['key'];
                break;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail du film</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #111;
            color: white;
        }
        .poster-wrapper {
            background-size: cover;
            background-position: center;
            padding: 50px;
            position: relative;
        }
        .poster-wrapper::before {
            content: '';
            background: rgba(0, 0, 0, 0.7);
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
        }
        .poster-content {
            position: relative;
            display: flex;
            gap: 30px;
            z-index: 1;
        }
        .film-image img {
            width: 250px;
            border-radius: 10px;
        }
        .film-information {
            max-width: 800px;
        }
        .film-information h1 {
            margin-top: 0;
        }
        .movie-voting {
            display: inline-block;
            background: #ff4444;
            padding: 5px 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-weight: bold;
        }
        .search-form {
            padding: 20px;
            text-align: center;
            background: #222;
        }
        input[type="text"] {
            padding: 10px;
            font-size: 16px;
            width: 300px;
        }
        input[type="submit"] {
            padding: 10px 15px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="search-form">
    <form method="GET">
        <input type="text" name="search" placeholder="Rechercher un film..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <input type="submit" value="Rechercher">
    </form>
</div>

<?php if ($movie_details): ?>
    <div class="poster-wrapper" style="background-image: url('https://image.tmdb.org/t/p/original<?= $movie_details['backdrop_path'] ?>');">
        <div class="poster-content">
            <div class="film-image">
                <img src="https://image.tmdb.org/t/p/w500<?= $movie_details['poster_path'] ?>" alt="Affiche du film">
            </div>
            <div class="film-information">
                <h1><?= $movie_details['title'] ?></h1>
                <div class="poster-release-date">Sortie : <?= $movie_details['release_date'] ?></div>
                <div class="poster-duration-genre">
                    <?= $movie_details['runtime'] ?> min |
                    <?= implode(", ", array_column($movie_details['genres'], 'name')) ?>
                </div>
                <p>
                    Réalisé par :
                    <?php
                    $realisateurs = array_filter($movie_details['credits']['crew'], fn($m) => $m['job'] === 'Director');
                    echo implode(", ", array_column($realisateurs, 'name'));
                    ?>
                    <br>
                    Avec :
                    <?php
                    $acteurs = array_slice($movie_details['credits']['cast'], 0, 4);
                    echo implode(", ", array_column($acteurs, 'name'));
                    ?>
                </p>
                <span class="movie-voting"><?= $movie_details['vote_average'] ?>/10</span>
                <p><?= $movie_details['overview'] ?></p>
            </div>
        </div>
        <div class="trailer">
            <?php if ($trailer_url): ?>
                <iframe src="<?= $trailer_url ?>" allowfullscreen></iframe>
            <?php else: ?>
                <p>Aucune bande-annonce disponible pour ce film.</p>
            <?php endif; ?>
        </div>
        <style>
            .trailer {
                width: 100%;
                display: flex;
                justify-content: center;
                margin-top: 20px;
                z-index: 10;
            }

            .trailer iframe {
                width: 80%;
                max-width: 800px;
                aspect-ratio: 16 / 9;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                z-index: 10;
            }
        </style>
    </div>
<?php elseif (isset($_GET['search'])): ?>
    <p style="text-align:center; padding:2rem;">Aucun résultat trouvé.</p>
<?php endif; ?>

</body>
</html>
