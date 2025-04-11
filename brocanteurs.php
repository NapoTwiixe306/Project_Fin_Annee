<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="./css/styles.css">
    <title>FAP - Brocanteurs</title>
</head>
<body>
    <header>
        <?php include './inc/navbar.inc.php';?>
    </header>
    <main>
        <section class="brocanteurs">
            <h2>Brocanteurs</h2>
            <section class="form">
                <form>
                    <input type="text" name="search" placeholder="Chercher un borcanteur...">
                    <select name="search" id="filter">
                        <option value="name">Nom</option>
                        <option value="Emplacement">Emplacement</option>
                        <option value="Type de stand">Stand</option>
                    </select>
                    <select name="filter" id="filter">
                        <option value="">Choisissez parmis les zones</option>
                        <option value="name">Zone A</option>
                        <option value="Emplacement">Zone B</option>
                        <option value="Type de stand">Zone C</option>
                        <option value="Type de stand">Zone D</option>
                        <option value="Type de stand">Zone E</option>
                    </select>
                </form>
            </section>
            <section class="card_brocanteurs">
                <article class="card">
                    <section class="infos">
                        <img src="https://avatar.iran.liara.run/public/12" alt="" width="50" height="50"/>
                        <div class="text">
                            <p class="title">Julien Milants</p>
                            <p>Violons</p>
                        </div>
                    </section>
                    <section class="badge">
                        <span class="emplacement">Emplacement A1</span>
                        <span class="zone">Zone A</span>
                    </section>
                    <section class="description">
                        <p>Spécialisé(e) dans meubles anciens. Venez découvrir
                        ses trésors uniques !</p>
                    </section>
                    <section class="button">
                        <button>Voir les objets</button>
                    </section>
                </article>
                <article class="card">
                    <section class="infos">
                        <img src="https://avatar.iran.liara.run/public/12" alt="" width="50" height="50"/>
                        <div class="text">
                            <p class="title">Julien Milants</p>
                            <p>Violons</p>
                        </div>
                    </section>
                    <section class="badge">
                        <span class="emplacement">Emplacement A1</span>
                        <span class="zone">Zone A</span>
                    </section>
                    <section class="description">
                        <p>Spécialisé(e) dans meubles anciens. Venez découvrir
                        ses trésors uniques !</p>
                    </section>
                    <section class="button">
                        <button>Voir les objets</button>
                    </section>
                </article>
                <article class="card">
                    <section class="infos">
                        <img src="https://avatar.iran.liara.run/public/12" alt="" width="50" height="50"/>
                        <div class="text">
                            <p class="title">Julien Milants</p>
                            <p>Violons</p>
                        </div>
                    </section>
                    <section class="badge">
                        <span class="emplacement">Emplacement A1</span>
                        <span class="zone">Zone A</span>
                    </section>
                    <section class="description">
                        <p>Spécialisé(e) dans meubles anciens. Venez découvrir
                        ses trésors uniques !</p>
                    </section>
                    <section class="button">
                        <button>Voir les objets</button>
                    </section>
                </article>
                <article class="card">
                    <section class="infos">
                        <img src="https://avatar.iran.liara.run/public/12" alt="" width="50" height="50"/>
                        <div class="text">
                            <p class="title">Julien Milants</p>
                            <p>Violons</p>
                        </div>
                    </section>
                    <section class="badge">
                        <span class="emplacement">Emplacement A1</span>
                        <span class="zone">Zone A</span>
                    </section>
                    <section class="description">
                        <p>Spécialisé(e) dans meubles anciens. Venez découvrir
                        ses trésors uniques !</p>
                    </section>
                    <section class="button">
                        <button>Voir les objets</button>
                    </section>
                </article>
                
            </section>
        </section>
    </main>
</body>
</html>