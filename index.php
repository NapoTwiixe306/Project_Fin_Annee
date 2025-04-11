<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/styles.css">
    <title>Échos de Violon - Page d'accueil</title>
</head>
<body>
    <header>
        <?php include './inc/navbar.inc.php'; ?>
    </header>
    <main class="main">
        <section class="hero_banner">
            <article class="left">
                <h2>Plongez dans l'univers du violon à la foire aux puces dédiée aux classique</h2>
                <p>La foire <strong>"Échos de Violon"</strong> est l’événement idéal pour les passionnés de musique classique et les amoureux du violon. Rejoignez-nous pour découvrir des instruments raffinés, des partitions rares, des accessoires de musique, et des objets de collection uniques liés au monde du violon. Que vous soyez un musicien en herbe, un collectionneur ou simplement curieux, vous trouverez des trésors qui raviront vos sens !</p>
            </article>
            <article class="right">
                <section class="icones">
                    <article class="date">
                        <img src="./img/svg/calendar.svg" alt="" width="50" height="50">
                        <p>15 Novembre 2025</p>
                    </article>
                    <article class="lieu">
                        <img src="./img/svg/location.svg" alt="" width="50" height="50">
                        <p>Palais 12, Bruxelles</p>
                    </article>
                    <article class="tarif">
                        <img src="./img/svg/price.svg" alt="" width="50" height="50">
                        <p>Voir Tarifs</p>
                    </article>
                </section>
            </article>
        </section>
        <section class="proposition">
            <h2>Ce qu'ils vendent</h2>
           <section class="images">
               <article class="card">
                    <h2>Objet 1</h2>
                    <p>20€</p>
                    <img src="https://placehold.co/300x200" alt="">
                    <p class="description">Description courte de l'objet 1</p>
                    <button>Voir les détails</button>
               </article>
               <article class="card">
                    <h2>Objet 1</h2>
                    <p>20€</p>
                    <img src="https://placehold.co/300x200" alt="">
                    <p class="description">Description courte de l'objet 1</p>
                    <button>Voir les détails</button>
               </article>
               <article class="card">
                    <h2>Objet 1</h2>
                    <p>20€</p>
                    <img src="https://placehold.co/300x200" alt="">
                    <p class="description">Description courte de l'objet 1</p>
                    <button>Voir les détails</button>
               </article>
           </section>
        </section>
        <section class="tarifs">
            <section class="cards">
                <article class="card">
                   <section class="info">
                       <h3>Petit Stand</h3>
                       <p class="price">49,99 € <span class="description">l'emplacement</span></p>
                       <button>Réservez votre stand maintenant</button>
                   </section>
                    <ul>
                        <li>
                            <img src="img/svg/check.svg" alt="Vérifié" class="check-icon">
                            Exposez 3 articles et gardez 100% de vos ventes.
                        </li>
                        <li>
                            <img src="img/svg/check.svg" alt="Vérifié" class="check-icon">
                            Idéal pour commencer.
                        </li>
                        <li>
                            <img src="img/svg/check.svg" alt="Vérifié" class="check-icon">
                            Accès rapide au marché local.
                        </li>
                    </ul>
                </article>

                <article class="card">
                    <section class="info">
                        <section class="populaire">
                            <h3>Stand Moyen</h3>
                            <p>Populaire</p>
                        </section>
                        <p class="price">99,99 € <span class="description">l'emplacement</span></p>
                        <button>Réservez votre stand maintenant</button>
                    </section>
                    <ul>
                        <li>
                            <img src="img/svg/check.svg" alt="Vérifié" class="check-icon">
                            Exposez jusqu'à 7 articles pour maximiser vos ventes.
                        </li>
                        <li>
                            <img src="img/svg/check.svg" alt="Vérifié" class="check-icon">
                            Emplacement privilégié pour une visiblité optimal.
                        </li>
                        <li>
                            <img src="img/svg/check.svg" alt="Vérifié" class="check-icon">
                            Accès à une zone de déballage plus proche.
                        </li>
                    </ul>
                </article>

                <article class="card">
                    <section class="info">
                        <h3>Grand Stand</h3>
                        <p class="price">249.99 € <span class="description">l'emplacement</span></p>
                        <button>Réservez votre stand maintenant</button>
                    </section>

                    <ul>
                        <li>
                            <img src="img/svg/check.svg" alt="Vérifié" class="check-icon">
                            Exposez autant d'articles que vous le souhaitez pour un impact maximum.
                        </li>
                        <li>
                            <img src="img/svg/check.svg" alt="Vérifié" class="check-icon">
                            Emplacement VIP: Vues directes pour tous les visiteurs.
                        </li>
                        <li>
                            <img src="img/svg/check.svg" alt="Vérifié" class="check-icon">
                            Installation prioritaire pour éviter les foules.
                        </li>
                    </ul>
                </article>
            </section>
        </section>
    </main>
    <?php include './inc/footer.inc.php'; ?>
</body>
</html>
