# Guide de configuration manuelle - GitHub Project PrestaShop Modules

Ce guide détaille toutes les étapes pour créer manuellement le GitHub Project si vous préférez ne pas utiliser le script automatique.

## 📋 Table des matières

1. [Création du projet](#1-création-du-projet)
2. [Configuration de la structure Kanban](#2-configuration-de-la-structure-kanban)
3. [Ajout des Iterations (sprints)](#3-ajout-des-iterations-sprints)
4. [Ajout des issues](#4-ajout-des-issues)
5. [Création des labels](#5-création-des-labels)
6. [Configuration des vues personnalisées](#6-configuration-des-vues-personnalisées)

---

## 1. Création du projet

### Étapes:

1. Aller sur votre page Projects: https://github.com/nenes25?tab=projects
2. Cliquer sur **"New project"**
3. Sélectionner le type **"Board"** (vue tableau Kanban)
4. Nommer le projet: **"PrestaShop Modules - Roadmap 2026"**
5. (Optionnel) Ajouter une description:
   ```
   Dashboard global pour suivre la planification, la progression, et les sprints mensuels 
   des développements sur les modules PrestaShop
   ```
6. Cliquer sur **"Create project"**

### Résultat attendu:
✅ Un nouveau projet vide avec une vue Board par défaut

---

## 2. Configuration de la structure Kanban

### Étapes:

1. Dans votre projet nouvellement créé, vous verrez des colonnes par défaut
2. Renommer/créer les colonnes suivantes (cliquer sur ⋮ → Rename):

   | Colonne | Description | Ordre |
   |---------|-------------|-------|
   | **Backlog** | Issues en attente de priorisation | 1 |
   | **À faire** | Issues priorisées pour le prochain sprint | 2 |
   | **En cours** | Issues en cours de développement | 3 |
   | **En revue** | Pull requests en cours de revue | 4 |
   | **Terminé** | Issues et PRs terminés | 5 |

3. Supprimer les colonnes inutiles par défaut (Todo, In Progress, Done si nécessaire)

### Conseils:
- Utilisez le drag & drop pour réorganiser les colonnes
- Chaque colonne peut avoir une couleur spécifique (Settings de la colonne)

---

## 3. Ajout des Iterations (sprints)

Les iterations permettent de planifier le travail sur des périodes définies (sprints mensuels).

### Étapes:

1. Dans le projet, cliquer sur **Settings** (⚙️) en haut à droite
2. Aller dans la section **"Fields"**
3. Cliquer sur **"+ New field"**
4. Sélectionner le type **"Iteration"**
5. Nommer le champ: **"Sprint"**

6. Configurer les iterations:

   #### Sprint Mars 2026
   - **Title**: Mars 2026
   - **Start date**: 2026-03-01 (1er mars 2026)
   - **Duration**: 4 weeks

   #### Sprint Avril 2026
   - **Title**: Avril 2026
   - **Start date**: 2026-04-01 (1er avril 2026)
   - **Duration**: 4 weeks

   #### Sprint Mai 2026
   - **Title**: Mai 2026
   - **Start date**: 2026-05-01 (1er mai 2026)
   - **Duration**: 4 weeks

7. Cliquer sur **"Save"**

### Résultat attendu:
✅ Un champ "Sprint" disponible sur chaque item du projet
✅ 3 sprints planifiés pour Q2 2026

---

## 4. Ajout des issues

### Méthode 1: Ajout manuel via l'interface

1. Dans le projet, cliquer sur **"+ Add item"** en bas de n'importe quelle colonne
2. Sélectionner **"Add item from repository"**
3. Chercher et sélectionner les issues par numéro

### Méthode 2: Recherche et ajout groupé

1. Utiliser la barre de recherche **"+ Add items"**
2. Filtrer par repository: `repo:nenes25/eicaptcha`
3. Sélectionner plusieurs issues en cochant les cases
4. Cliquer sur **"Add selected items"**

### Liste des issues à ajouter:

#### eicaptcha (nenes25/eicaptcha)
Issues: #331, #329, #328, #320, #319, #318, #314, etc.

#### prestashop_console (nenes25/prestashop_console)
Issues: #251, #245, #239, #238, #234, #232, #127, #120, #92, #5

#### hhpsmigrationupgradedb (nenes25/hhpsmigrationupgradedb)
Issues: #19, #18, #3

#### hhmodulesmanager (nenes25/hhmodulesmanager)
Issues: #22, #20, #16, #14

#### hhmodulescatalogapi (nenes25/hhmodulescatalogapi)
Issues: #6, #4, #1

#### cronjobs (nenes25/cronjobs)
À définir selon les besoins

### Organisation:
- Placer les issues prioritaires dans **"À faire"**
- Attribuer le sprint **"Mars 2026"** aux issues à démarrer rapidement
- Mettre le reste dans **"Backlog"**

---

## 5. Création des labels

Les labels doivent être créés dans **chaque dépôt** individuellement.

### Étapes pour chaque dépôt:

1. Aller sur le dépôt (ex: https://github.com/nenes25/eicaptcha)
2. Cliquer sur **"Issues"** → **"Labels"**
3. Cliquer sur **"New label"**

### Labels à créer:

#### Catégorie: Priorité

| Nom | Couleur (hex) | Description |
|-----|---------------|-------------|
| `priority:high` | `d73a4a` | Priorité haute - À traiter en urgence |
| `priority:medium` | `fbca04` | Priorité moyenne - À planifier |
| `priority:low` | `0e8a16` | Priorité basse - Nice to have |

#### Catégorie: Type

| Nom | Couleur (hex) | Description |
|-----|---------------|-------------|
| `bug` | `d73a4a` | Bug ou erreur à corriger |
| `enhancement` | `a2eeef` | Nouvelle fonctionnalité ou amélioration |
| `documentation` | `0075ca` | Amélioration de la documentation |
| `testing` | `d876e3` | Tests et qualité du code |

#### Catégorie: Compatibilité

| Nom | Couleur (hex) | Description |
|-----|---------------|-------------|
| `prestashop-9` | `5319e7` | Compatible PrestaShop 9.x |
| `php-8.x` | `7057ff` | Compatible PHP 8.x |

#### Catégorie: Workflow

| Nom | Couleur (hex) | Description |
|-----|---------------|-------------|
| `need-feedback` | `d4c5f9` | Besoin de retour ou clarification |
| `ready-to-dev` | `c5def5` | Spécifications claires, prêt pour dev |

### Astuce:
Utilisez le script automatique `setup-project.sh` pour créer tous les labels automatiquement dans tous les dépôts!

---

## 6. Configuration des vues personnalisées

### Vue 1: Kanban (défaut)

1. Cette vue est créée automatiquement
2. Configuration:
   - **Layout**: Board
   - **Group by**: Status
   - Affiche les colonnes: Backlog, À faire, En cours, En revue, Terminé

### Vue 2: Roadmap (Timeline)

1. Cliquer sur la vue actuelle → **"+ New view"**
2. Nommer: **"Roadmap"**
3. Choisir layout: **"Roadmap"**
4. Configuration:
   - **Slice by**: Iteration (Sprint)
   - **Timeline**: 3 mois (Mars - Mai 2026)
   - **Zoom**: Par semaine
5. Sauvegarder

### Vue 3: Par dépôt

1. Créer une nouvelle vue
2. Nommer: **"Par dépôt"**
3. Choisir layout: **"Table"**
4. Configuration:
   - **Group by**: Repository
   - **Sort by**: Priority (descendant)
   - Colonnes visibles: Title, Status, Sprint, Labels, Assignees
5. Sauvegarder

### Vue 4: Par priorité

1. Créer une nouvelle vue
2. Nommer: **"Par priorité"**
3. Choisir layout: **"Table"**
4. Configuration:
   - **Group by**: Labels (filtrer sur priority:*)
   - **Sort by**: Created date (descendant)
   - Colonnes visibles: Repository, Title, Status, Sprint
5. Sauvegarder

---

## ✅ Checklist finale

Après avoir suivi ce guide, vous devriez avoir:

- [ ] Un projet GitHub nommé "PrestaShop Modules - Roadmap 2026"
- [ ] 5 colonnes Kanban configurées
- [ ] 3 sprints mensuels (Mars, Avril, Mai 2026)
- [ ] Issues des 6 dépôts ajoutées au projet
- [ ] Labels créés dans tous les dépôts concernés
- [ ] 4 vues personnalisées: Kanban, Roadmap, Par dépôt, Par priorité
- [ ] Issues prioritaires assignées au sprint Mars 2026

---

## 🎯 Bonnes pratiques

### Utilisation quotidienne:

1. **Déplacer les cartes** entre colonnes selon leur avancement
2. **Assigner les issues** aux développeurs responsables
3. **Mettre à jour les sprints** pour planifier le travail
4. **Ajouter des labels** pour faciliter le tri et les recherches
5. **Lier les PRs aux issues** pour un suivi automatique

### Workflow recommandé:

```
Backlog → À faire → En cours → En revue → Terminé
    ↓         ↓          ↓          ↓
 Sprint    Sprint    Sprint     Auto
planning  start    reviewing  close
```

### Maintenance hebdomadaire:

- Revue du Backlog (priorisation)
- Mise à jour de la Roadmap
- Déplacement des issues selon avancement
- Planning du prochain sprint

---

## 📞 Besoin d'aide?

- [Documentation GitHub Projects](https://docs.github.com/fr/issues/planning-and-tracking-with-projects)
- [Guide des iterations](https://docs.github.com/en/issues/planning-and-tracking-with-projects/understanding-fields/about-iteration-fields)
- [Automatisation avec GitHub Actions](https://docs.github.com/en/issues/planning-and-tracking-with-projects/automating-your-project)

---

**Bon courage dans la mise en place de votre dashboard! 🚀**
