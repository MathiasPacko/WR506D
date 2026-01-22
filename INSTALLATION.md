# 🚀 Guide d'installation - Projet Rellflix

Ce projet est composé de **2 applications séparées** qui doivent être lancées ensemble.

## 📦 Repositories

- **Backend (API Symfony)** : `git@github.com:MathiasPacko/WR506D.git`
- **Frontend (Vue.js)** : `git@github.com:MathiasPacko/WR505D.git`

---

## ⚙️ Installation complète

### Étape 1 : Cloner les 2 repositories

```bash
# Créer un dossier pour le projet
mkdir rellflix-projet
cd rellflix-projet

# Cloner le backend
git clone git@github.com:MathiasPacko/WR506D.git
git checkout develop

# Cloner le frontend
git clone git@github.com:MathiasPacko/WR505D.git
git checkout develop
```

---

### Étape 2 : Installer le BACKEND (WR506D)

```bash
cd WR506D

# Installer les dépendances PHP
composer install

# Configurer la base de données
# Éditer le fichier .env et modifier la ligne DATABASE_URL
# Exemple : DATABASE_URL="mysql://root:root@127.0.0.1:3306/buts5"

# Créer la base de données
php bin/console doctrine:database:create

# Lancer les migrations
php bin/console doctrine:migrations:migrate

# Charger les données de test (201 films, 201 acteurs, etc.)
php bin/console doctrine:fixtures:load

# Lancer le serveur Symfony sur le port 8000
symfony server:start -d
# OU si symfony CLI n'est pas installé :
php -S 127.0.0.1:8000 -t public
```

✅ **Le backend est maintenant accessible sur : http://127.0.0.1:8000**

---

### Étape 3 : Installer le FRONTEND (WR505D)

```bash
# Ouvrir un nouveau terminal
cd ../WR505D

# Installer les dépendances Node.js
npm install

# Lancer le serveur de développement
npm run dev
```

✅ **Le frontend est maintenant accessible sur : http://localhost:5173**

---

## 🔐 Se connecter

Ouvrez votre navigateur sur **http://localhost:5173**

### Compte Administrateur
- **Email** : `admin@example.com`
- **Mot de passe** : `admin123`

### Compte Utilisateur
- **Email** : `test@example.com`
- **Mot de passe** : `password123`

---

## 📊 Fonctionnalités

### Pages publiques (sans connexion)
- 🏠 Accueil avec 5 films en tendance
- 🎬 Liste complète des films avec recherche
- 🎭 Liste des acteurs
- 📄 Détails de chaque film et acteur

### Pages admin (avec connexion)
- 📊 Tableau de bord avec statistiques
- ✏️ CRUD Films (nom, description, durée, budget, catégories, acteurs)
- ✏️ CRUD Acteurs (prénom, nom, date de naissance, nationalité)
- ✏️ CRUD Catégories
- ✏️ CRUD Utilisateurs

---

## 🛠️ Technologies utilisées

### Backend
- PHP 8.1+
- Symfony 6.4
- API Platform
- JWT Authentication
- Doctrine ORM
- MySQL

### Frontend
- Vue 3 (Composition API)
- Vite
- Vue Router 4
- Pinia (state management)
- Axios
- Tailwind CSS
- Thème Netflix (noir/rouge)

---

## 🐛 Dépannage

### Problème : Le frontend ne se connecte pas au backend

**Vérifier que :**
- Le backend est bien lancé sur `http://127.0.0.1:8000`
- Le frontend est sur `http://localhost:5173`
- Les deux serveurs tournent en même temps

### Problème : Erreur 401 (Unauthorized)

**Solution :** Les tokens JWT expirent après 1 heure. Déconnectez-vous et reconnectez-vous.

### Problème : Pas de films/acteurs affichés

**Solution :** Vérifier que les fixtures ont été chargées :
```bash
cd WR506D
php bin/console doctrine:fixtures:load
```

### Problème : Base de données introuvable

**Solution :** Vérifier le fichier `.env` dans WR506D et s'assurer que `DATABASE_URL` est correct.

---

## 📝 Notes importantes

- ⚠️ **Les 2 serveurs doivent tourner en même temps** pour que l'application fonctionne
- ⚠️ **Ne jamais pousser sur `main` directement**, toujours travailler sur `develop`
- ⚠️ **Port 8000** : Backend Symfony
- ⚠️ **Port 5173** : Frontend Vue.js

---

## 📧 Support

En cas de problème, contacter : **[votre email]**

---

**Projet développé en 2025 - BUTS5**
