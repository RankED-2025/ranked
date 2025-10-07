# Projet Ranked

Ce projet est composé de deux parties :

- **Front-end** : développé avec [Vue.js](https://vuejs.org/), situé dans le dossier [`front/`](front/)
- **Back-end** : développé avec [Symfony](https://symfony.com/), situé dans le dossier [`back/`](back/)

## Structure du projet

```
back/   # Symfony (API, logique métier)
front/  # Vue.js (interface utilisateur)
```

## Installation

Depuis la racine du projet, lancez :

```sh
npm run install:all
```

Cela installera toutes les dépendances nécessaires pour le back-end (Symfony) et le front-end (Vue.js).

## Lancement du projet en développement

Toujours depuis la racine du projet :

```sh
npm run start:dev
```

Cela démarre simultanément le serveur Symfony (back) et le serveur de développement Vue.js (front).