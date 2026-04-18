# Mon Projet d'Énergie ⚡

Bonjour ! C'est mon projet pour suivre la consommation d'énergie (électricité, eau, gaz). C'est un projet simple fait avec Laravel et React.

## C'est quoi ce projet ?
Ce projet aide les gens à voir combien d'énergie ils utilisent. On peut ajouter des compteurs et noter les lectures chaque mois.

## Les technologies
- **Backend** : Laravel (PHP)
- **Frontend** : React avec Vite
- **Base de données** : MySQL
- **Docker** : Pour lancer le projet facilement

## Comment lancer le projet avec Docker

C'est très facile ! Il faut juste avoir Docker sur ton ordinateur.

1. Ouvre ton terminal dans le dossier du projet.
2. Tape cette commande :
   ```bash
   docker compose up --build
   ```
## Configuration de la base de données

Après avoir lancé le projet avec Docker, il faut exécuter les migrations pour créer les tables dans la base de données.

1. Ouvre un nouveau terminal.
2. Tape cette commande :

```bash
docker compose exec app php artisan migrate && php artisan db:seed
```

3. Attends un peu que tout se prépare.
4. Le site sera disponible sur : `http://localhost:3000`

## Ce que le projet fait (Fonctions)
- On peut créer un compte et se connecter.
- On peut ajouter ses compteurs d'énergie.
- On peut voir des graphiques simples de sa consommation.
- Il y a une partie Admin pour voir tout le système.

C'est mon premier "vrai" projet, j'espère que c'est bien ! 😊
