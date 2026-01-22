# Guide d'installation complète - Rellflix

Ce guide explique comment installer et lancer le projet complet (Backend Symfony + Frontend Vue.js).

## 📦 Architecture du projet

Le projet est composé de 2 applications séparées :

1. **Backend (wr506d)** - API Symfony avec API Platform
2. **Frontend (wr505d)** - Application Vue.js

## 🚀 Installation complète

### 1. Backend Symfony (wr506d)

```bash
# Aller dans le dossier backend
cd wr506d

# Installer les dépendances
composer install

# Configurer la base de données
# Éditer le fichier .env et configurer DATABASE_URL

# Créer la base de données
php bin/console doctrine:database:create

# Lancer les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures (données de test)
php bin/console doctrine:fixtures:load

# Lancer le serveur Symfony
symfony server:start
# OU
php -S 127.0.0.1:8000 -t public
```

Le backend sera accessible sur : `http://127.0.0.1:8000`

### 2. Frontend Vue.js (wr505d)

```bash
# Aller dans le dossier frontend
cd ../wr505d

# Installer les dépendances
npm install

# Lancer le serveur de développement
npm run dev
```

Le frontend sera accessible sur : `http://localhost:5173`

## 🔐 Connexion

Une fois les deux serveurs lancés, vous pouvez vous connecter avec :

**Administrateur :**
- Email : `admin@example.com`
- Mot de passe : `admin123`

**Utilisateur :**
- Email : `test@example.com`
- Mot de passe : `password123`

## 📊 Données de test

Les fixtures chargent automatiquement :
- ✅ ~200 films
- ✅ ~200 acteurs
- ✅ ~800 relations acteur-film
- ✅ Plusieurs catégories
- ✅ 2 utilisateurs de test

## 🛠️ Commandes utiles

### Backend
```bash
# Voir les routes API
php bin/console debug:router

# Créer un nouvel utilisateur admin
php bin/console security:hash-password

# Vider le cache
php bin/console cache:clear
```

### Frontend
```bash
# Build pour production
npm run build

# Preview du build
npm run preview

# Lancer les tests
npm run test:e2e
```

## 🔗 URLs importantes

- **Frontend** : http://localhost:5173
- **Backend API** : http://127.0.0.1:8000/api
- **Documentation API** : http://127.0.0.1:8000/api/docs

## ⚙️ Configuration

### CORS (déjà configuré)
Le backend autorise les requêtes depuis `http://localhost:5173`

### JWT (déjà configuré)
- Les tokens expirent après 1 heure
- Endpoint de login : `/api/login_check`

## 📝 Fonctionnalités principales

### Frontend
- Thème Netflix (noir/rouge)
- Navigation publique (films, acteurs)
- Interface admin complète (CRUD)
- Recherche de films
- Authentification JWT

### Backend
- API REST avec API Platform
- Authentification JWT
- Validation des données
- Relations complexes (ManyToMany)
- Accès public en lecture

## 🐛 Dépannage

### Le frontend ne se connecte pas au backend
- Vérifier que le backend est lancé sur `http://127.0.0.1:8000`
- Vérifier la configuration CORS dans `config/packages/nelmio_cors.yaml`

### Erreur 401 (Unauthorized)
- Les tokens JWT expirent après 1h
- Se reconnecter pour obtenir un nouveau token

### Pas de données
- Vérifier que les fixtures ont été chargées : `php bin/console doctrine:fixtures:load`

---

**Développé en 2025 - Projet BUTS5**
