# 🎓 Préparation à la Soutenance : Energy App (Suivi de Consommation)

Ce document est ta trame complète pour créer tes slides PowerPoint et préparer ton discours oral pour la soutenance de ton projet "Energy App" (basé sur Laravel, React et Docker).

---

## 1. Présentation générale du projet

*   **Titre du projet** : *EcoTrack* (ou le nom exact de ton Energy App)
*   **Description courte** : Une application web moderne de gestion et de suivi de la consommation d'énergie, permettant aux utilisateurs de suivre leurs relevés avec précision selon leur localisation (ville/quartier).
*   **Domaine du projet** : Green IT / Gestion de l'énergie / Développement Web Full-Stack.
*   **Public cible** : Particuliers et foyers souhaitant surveiller, analyser et optimiser leur consommation d'énergie pour réduire leurs factures et leur empreinte écologique.

## 2. Problématique

*   **Quel problème résout le projet ?** : Les ménages manquent souvent de visibilité en temps réel sur leur consommation d'énergie. La réception d'une facture annuelle ou mensuelle arrive souvent trop tard pour ajuster ses habitudes.
*   **Pourquoi ce problème est important ?** : Dans le contexte actuel de transition écologique et d'inflation des coûts de l'énergie, maîtriser sa consommation est devenu un enjeu financier et environnemental majeur.
*   **Limites des solutions traditionnelles** : Les solutions classiques (relevés manuels papier ou portails fournisseurs obsolètes) manquent d'intuitivité, ne proposent pas d'interface moderne et sont rarement géolocalisées avec précision (par quartier).

## 3. Solution proposée

*   **Comment fonctionne le système ?** : Le système offre une plateforme centralisée où les utilisateurs s'inscrivent en renseignant leur localisation exacte (ville, quartier). Ils peuvent ensuite saisir leurs relevés de consommation et visualiser des tableaux de bord analytiques.
*   **Quels sont les avantages de la solution ?** :
    *   Une interface unifiée, moderne et apaisante (Thème "Émeraude / Énergie").
    *   Une localisation fine permettant à terme des analyses comparatives (par quartier).
*   **Pourquoi cette solution est efficace ?** : L'utilisation d'une Single Page Application (React) garantit une navigation fluide sans rechargements frustrants, incitant l'utilisateur à se connecter régulièrement.

## 4. Objectifs du projet

*   **Objectif principal** : Développer un outil complet, fiable et facile d'utilisation pour aider à la réduction de la consommation énergétique.
*   **Objectifs secondaires** :
    *   Mettre en place une architecture découplée robuste (API REST).
    *   Assurer un déploiement fiable via conteneurisation (Docker).
    *   Offrir un parcours utilisateur (UX) sans faille, notamment sur les formulaires d'authentification complexes.
*   **Résultats attendus** : Une plateforme fonctionnelle, déployable en un clic grâce à Docker, avec une base de données parfaitement synchronisée.

## 5. Technologies utilisées

*   **Laravel (PHP)**
    *   *Rôle* : Framework Backend, création de l'API REST, ORM (Eloquent), Gestion de base de données.
    *   *Avantages* : Sécurisé, structuré, excellente gestion des relations et des migrations de base de données.
    *   *Pourquoi l'avoir choisi* : Pour construire une API solide capable de traiter les règles métier avec rigueur.
*   **React (JavaScript)**
    *   *Rôle* : Framework Frontend (Interface utilisateur).
    *   *Avantages* : Rendu dynamique (Virtual DOM), architecture par composants (ex: liste déroulante des villes réutilisable).
*   **TailwindCSS**
    *   *Rôle* : Framework CSS utilitaire.
    *   *Pourquoi l'avoir choisi* : A permis d'implémenter rapidement un design system cohérent et moderne (Thème Émeraude) sur l'ensemble de l'application.
*   **MySQL**
    *   *Rôle* : Base de données relationnelle.
    *   *Avantages* : Parfait pour gérer les relations hiérarchiques (Villes -> Quartiers -> Utilisateurs -> Relevés).
*   **Docker & Docker-Compose**
    *   *Rôle* : Conteneurisation de l'application.
    *   *Avantages* : Garantit que l'application s'exécute de manière identique sur n'importe quelle machine (Dev ou Prod) en isolant le Backend, le Frontend et la DB.

## 6. Architecture technique

*   **Architecture générale** : Client-Serveur découplé, entièrement conteneurisé sous Docker.
*   **Communication frontend/backend** : Le frontend (React) sur le port 3000 communique avec l'API backend (Laravel) sur le port 8000 en échangeant des requêtes HTTP au format JSON (via Axios).
*   **API** : API RESTful protégeant les routes sensibles.
*   **Base de données** : Base de données MySQL 8.0 isolée dans son propre conteneur (`energy-db`), avec un volume persistant pour ne pas perdre les données au redémarrage.
*   **Sécurité & Authentification** : Authentification par tokens gérée de façon sécurisée entre Laravel et le State React, gestion stricte des erreurs de formulaires.
*   **Déploiement** : Utilisation d'un fichier `docker-compose.yml` orchestrant un réseau interne (`energy-network`) et exécutant automatiquement les migrations de base de données au lancement (`php artisan migrate --force --seed`).

## 7. Fonctionnalités principales

*   **Authentification Avancée (Login/Register)** : Inscription dynamique où le choix de la ville met à jour instantanément la liste des quartiers disponibles.
*   **Tableau de Bord (Dashboard)** : Espace unifié permettant d'avoir une vue d'ensemble de son compte et de ses statistiques de consommation.
*   **CRUD des Relevés (Readings)** : Interface permettant à l'utilisateur d'ajouter, modifier, supprimer et visualiser ses relevés énergétiques.
*   **Gestion des États Globaux** : Utilisation de Redux (ReadingsSlice) pour gérer les données de l'application de manière centralisée côté client sans avoir à faire d'appels API répétitifs.

## 8. Base de données

*   **Tables principales** :
    1.  `users` (id, nom, email, password, quartier_id)
    2.  `cities` (id, nom)
    3.  `quartiers` (id, nom, city_id)
    4.  `readings` / relevés (id, user_id, valeur_consommation, date_releve)
*   **Relations** :
    *   Une *Ville* contient plusieurs *Quartiers* (1:N).
    *   Un *Quartier* contient plusieurs *Utilisateurs* (1:N).
    *   Un *Utilisateur* possède plusieurs *Relevés* (1:N).
*   **Explication simplifiée pour le jury** : "Notre base est pensée géographiquement. Chaque relevé de consommation appartient à un utilisateur, qui lui-même est rattaché à un quartier précis. Cette structure en entonnoir permettra à l'avenir de générer des statistiques précises par zone géographique."

## 9. UML / Conception (À schématiser sur vos slides)

*   **Use Case (Cas d\'utilisation)** : L'acteur "Utilisateur" peut -> Créer un compte avec sa localisation, Saisir une consommation, Consulter son Dashboard.
*   **Diagramme de classes** : Représentation visuelle des tables (City, Quartier, User, Reading) pour montrer votre maîtrise des clés étrangères.
*   **Architecture logicielle** : 3 blocs distincts -> Bloc Frontend (React) <==> Bloc API (Laravel) <==> Bloc DB (MySQL). Le tout encapsulé dans le grand bloc "Docker".

## 10. Difficultés rencontrées (et résolues)

*   **Problème de l'Authentification et des Dropdowns** : À l'inscription, la liste des villes et quartiers s'affichait vide.
    *   *Solution* : Analyse du réseau (Network tab), correction des chemins de l'API (endpoints) et bonne gestion de l'état asynchrone dans React pour charger les quartiers *après* la sélection de la ville.
*   **Conflits d'états d'erreur Frontend** : Les erreurs de connexion du composant Login "fuitaient" et s'affichaient sur la page Register, et inversement.
    *   *Solution* : Implémentation d'un nettoyage rigoureux de l'état (cleanup) au moment où l'utilisateur quitte la page (démontage du composant).
*   **Déploiement et Commandes Artisan** : Des erreurs "Could not open input file: artisan" empêchaient l'exécution des commandes.
    *   *Solution* : Mise en place rigoureuse de Docker. Le fichier `docker-compose.yml` a été configuré pour exécuter les migrations (`migrate --force`) directement dans le bon contexte (conteneur backend), résolvant définitivement les problèmes d'environnement local.

## 11. Résultats obtenus

*   **Ce qui fonctionne** : L'ensemble du processus métier est opérationnel. La base de données, le back et le front communiquent parfaitement au sein de l'environnement Docker.
*   **Expérience utilisateur** : L'application possède une véritable identité visuelle avec son thème Émeraude qui donne une sensation "Green" et "Pro", avec une gestion des erreurs claire pour l'utilisateur.
*   **Sécurité et Stabilité** : Grâce à Docker, l'application est devenue robuste : les problèmes de connexion à la base de données (localhost vs conteneur) ont été définitivement réglés.

## 12. Améliorations futures

*   **Intégration IoT** : Connecter l'API directement aux compteurs intelligents (ex: Linky) pour une remontée automatique des données.
*   **Gamification** : Ajouter des fonctionnalités de comparaison de consommation moyenne par quartier pour inciter les utilisateurs à faire mieux que leurs voisins (de manière anonyme).
*   **Alertes automatiques** : Envoi de notifications ou d'emails automatiques en cas de pic anormal de consommation.

## 13. Conclusion professionnelle

"Pour conclure, concevoir 'Energy App' a été une excellente opportunité d'appliquer des concepts avancés de développement. Au-delà du code pur, j'ai appris à orchestrer des services avec Docker et à résoudre des problèmes d'architecture complexes, comme la gestion des états partagés entre différentes vues React. J'ai atteint l'objectif de fournir une application utile, esthétique, et surtout techniquement solide. Ce projet confirme ma passion pour le développement de solutions web qui ont du sens, et m'a préparé aux réalités du travail d'équipe et des environnements de production."

---

## 14. Questions possibles pendant la soutenance (et vos réponses)

**Q1 (Choix technologiques) : Pourquoi avoir mis votre projet sous Docker ? N'était-ce pas trop compliqué pour une simple application ?**
*Réponse :* C'était un investissement initial qui a payé. Avant Docker, j'avais des problèmes de configuration entre la base de données et l'API (erreurs de connexion), et des soucis de dossiers avec les commandes Artisan. Docker m'assure que "si ça marche chez moi, ça marchera sur le serveur de production", car l'environnement est figé dans le conteneur.

**Q2 (Technique) : Comment avez-vous géré la dépendance entre la liste des Villes et des Quartiers ?**
*Réponse :* C'est géré côté Frontend. Lors du chargement de la page, j'appelle l'API pour récupérer toutes les villes. Quand l'utilisateur sélectionne une ville, un événement `onChange` est déclenché. Il met à jour l'état de la ville choisie, et déclenche immédiatement un deuxième appel API ciblé (ex: `/api/cities/{id}/quartiers`) pour récupérer uniquement les quartiers pertinents.

**Q3 (UX/UI) : Pourquoi avoir utilisé TailwindCSS plutôt que d'écrire votre propre CSS ?**
*Réponse :* Tailwind m'a permis de mettre en place extrêmement rapidement un "Design System" cohérent. Par exemple, pour donner l'identité "Green/Energy" au projet, il m'a suffi d'utiliser la palette "Emerald" de Tailwind partout, garantissant une uniformité visuelle sans avoir à gérer des dizaines de fichiers de style encombrants.

**Q4 (Piège) : Si demain l'application gagne 100 000 utilisateurs, la page "Dashboard" ne va-t-elle pas faire exploser la base de données en calculant toutes les consommations ?**
*Réponse :* Actuellement, les requêtes sont directes. Pour passer à l'échelle, on mettrait en place un système de "Mise en cache" (ex: Redis). Les statistiques d'un utilisateur seraient calculées une fois par jour ou à chaque nouvel ajout de relevé, et stockées en cache pour un affichage instantané sans solliciter MySQL.

---

## 15. Script oral de soutenance (Modèle à adapter)

**[Slide 1 : Titre]**
"Bonjour à tous. J'ai le plaisir de vous présenter aujourd'hui mon projet de fin d'année : une application de suivi de consommation énergétique nommée EcoTrack."

**[Slide 2 : Problématique]**
"Le constat qui a motivé ce projet est clair : aujourd'hui, nous devons tous réduire notre consommation énergétique. Pourtant, les outils à la disposition des particuliers pour suivre leur consommation au jour le jour sont rares, souvent obsolètes, et manquent de précision géographique."

**[Slide 3 : Solution]**
"C'est pourquoi j'ai conçu une plateforme moderne et centralisée. L'idée est d'offrir à l'utilisateur un tableau de bord intuitif où il peut, très rapidement, renseigner ses relevés de compteurs et voir ses tendances."

**[Slide 4 : Technologies]**
"Techniquement, j'ai opté pour une architecture solide : Laravel en backend pour gérer les règles métier rigoureuses, et React en frontend pour offrir une navigation ultra-fluide, habillée grâce à TailwindCSS aux couleurs de l'énergie (Thème Émeraude)."

**[Slide 5 : Architecture Docker]** *(Montrer le schéma Docker Compose)*
"Une de mes fiertés sur ce projet a été la mise en place de Docker. Comme vous pouvez le voir, l'application est divisée en trois conteneurs isolés : un pour l'interface, un pour l'API, et un pour la base de données. Cela rend le projet extrêmement professionnel et facilement déployable."

**[Slide 6 : Démo / Formulaire d'inscription]**
*(Passez en revue l'application, montrez particulièrement la sélection dynamique Ville/Quartier à l'inscription et expliquez que l'UX est pensée pour être sans friction)*

**[Slide 7 : Difficultés Techniques]**
"Ce projet n'a pas été sans embûches. J'ai notamment été confronté à des problèmes de conflits d'états dans React entre les pages de connexion et d'inscription, où les erreurs s'affichaient au mauvais endroit. J'ai dû plonger dans le cycle de vie des composants React pour nettoyer proprement le 'State' au changement de page."

**[Slide 8 : Améliorations Futures]**
"L'application est aujourd'hui une base très solide. La prochaine étape logique serait d'automatiser la remontée des données en se connectant aux API des compteurs communicants, et de créer des graphiques comparatifs entre utilisateurs d'un même quartier."

**[Slide 9 : Conclusion]**
"En conclusion, ce projet a été extrêmement formateur, tant sur la résolution de bugs d'architecture que sur la conteneurisation. Je suis prêt à transposer ces acquis dans le monde professionnel. Merci de votre écoute, je suis à l'écoute de vos questions."

---

## 16. Conseils pour la présentation

*   **Cohérence visuelle (Très important)** : Ton application a un thème visuel défini ("Emerald/Energy" comme mentionné dans tes refontes d'UI). Tes slides PowerPoint **DOIVENT** reprendre ces mêmes couleurs (des nuances de vert, de gris clair, un design minimaliste).
*   **Mise en avant technique** : Ton atout principal est la conteneurisation. Fais un beau schéma de ton `docker-compose.yml` (avec des icônes pour le Front, le Back et la DB). C'est très apprécié par les jurys techniques.
*   **Parle des erreurs corrigées** : Ne cache pas tes difficultés passées (les dropdowns vides, les commandes artisan qui plantaient). Au contraire, explique avec fierté *comment* tu les as identifiées et résolues (c'est ce qu'un recruteur recherche).
*   **Attitude** : Tu maîtrises l'écosystème Laravel + React + Docker, ce qui est une excellente stack. Aie confiance en ton code. Prends le temps de bien respirer entre chaque slide.
