# ⚡ EnergyApp - Suivi de Consommation Énergétique

Bienvenue sur **EnergyApp** ! 🌿
Il s'agit d'une application simple et efficace pour suivre et gérer votre consommation d'énergie (électricité, eau, gaz). Le but est d'aider les citoyens à mieux comprendre leurs dépenses énergétiques.

---

## 🎯 Objectif du Projet

Le projet a un but simple : **aider les gens à économiser de l'énergie.**
Avec cette application, vous pouvez :
- Enregistrer vos compteurs.
- Suivre votre consommation mois par mois.
- Voir des statistiques claires pour réduire vos factures.

---

## 🛠️ Technologies Utilisées

Nous utilisons des technologies modernes mais simples pour ce projet :

- **Backend** : Laravel (PHP)
- **Frontend** : React (JavaScript)
- **Base de données** : MySQL
- **Docker** : Pour lancer le projet facilement sur n'importe quel ordinateur.

---

## 🚀 Installation (Guide pas à pas)

Suivez ces étapes pour lancer le projet sur votre ordinateur :

### 1. Cloner le projet
Ouvrez votre terminal et tapez :
```bash
git clone https://github.com/amine-elhanchaoui/energy-app.git
cd energy-app
```

### 2. Lancer les conteneurs Docker
Lancez Docker Desktop, puis tapez cette commande :
```bash
docker compose up -d --build
```
*Note : Attendez quelques minutes pour la première installation.*

### 3. Lancer la base de données (Migrations & Seed)
Pour créer les tables et les données initiales, tapez :
```bash
docker exec -it energy-backend php artisan migrate --seed
```

### 4. Accéder à l'application
- **Frontend (Site web)** : [http://localhost:3000](http://localhost:3000)
- **Backend (API)** : [http://localhost:8000](http://localhost:8000)

---

## ✨ Fonctionnalités

### 👤 Pour les Citoyens
- **Inscription & Connexion** : Créez votre compte personnel.
- **Gestion des Compteurs** : Ajoutez vos compteurs (Électricité, Eau, Gaz).
- **Saisie de Consommation** : Notez vos index chaque mois.
- **Tableau de Bord** : Visualisez vos graphiques de consommation.

### 🔑 Pour les Administrateurs
- **Gestion des Utilisateurs** : Voir la liste des inscrits.
- **Statistiques Globales** : Voir la consommation totale de la ville.
- **Configuration** : Gérer les types de compteurs et les tarifs.

---

## 📸 Captures d'écran

*(Les images arrivent bientôt !)*

| Espace Admin | Dashboard Admin |
| :---: | :---: |
| ![Espace Admin](storage/app/public/Adminspace.png) | ![Dashboard Admin](storage/app/public/AdminDashboard.png) |

---

## 👨‍💻 Auteur

Ce projet a été réalisé avec ❤️ par :
- **Amine** - *Développeur Fullstack Junior*

---
*Si vous aimez ce projet, n'hésitez pas à mettre une ⭐ sur GitHub !*
