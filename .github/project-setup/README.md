# GitHub Project Setup - PrestaShop Modules Roadmap 2026

Ce dossier contient les outils et la configuration pour créer et gérer un GitHub Project global pour tous les modules PrestaShop de @nenes25.

## 📋 Vue d'ensemble

Le projet GitHub permettra de suivre:
- 🎯 La planification et les sprints mensuels
- 📊 La progression des développements
- 🔄 L'état des issues et pull requests
- 🗂️ La gestion centralisée de 6 modules PrestaShop

### Modules concernés

1. **eicaptcha** - Module de captcha
2. **prestashop_console** - Console CLI pour PrestaShop
3. **hhpsmigrationupgradedb** - Migration et upgrade de base de données
4. **hhmodulesmanager** - Gestionnaire de modules
5. **cronjobs** - Gestion des tâches planifiées
6. **hhmodulescatalogapi** - API catalogue de modules

## 🚀 Installation rapide

### Prérequis

1. **GitHub CLI** installé et authentifié
   ```bash
   # Installation (Ubuntu/Debian)
   sudo apt install gh
   
   # Installation (macOS)
   brew install gh
   
   # Authentification
   gh auth login
   ```

2. **jq** pour le traitement JSON
   ```bash
   # Ubuntu/Debian
   sudo apt install jq
   
   # macOS
   brew install jq
   ```

### Utilisation du script automatique

```bash
# Se placer dans le dossier du script
cd .github/project-setup

# Exécuter le script
./setup-project.sh
```

Le script va:
1. ✅ Créer le projet GitHub "PrestaShop Modules - Roadmap 2026"
2. ✅ Créer les labels dans tous les dépôts
3. ✅ Ajouter les issues existantes au projet
4. ℹ️ Afficher les instructions pour les étapes manuelles restantes

## 📝 Configuration manuelle post-installation

Certaines configurations doivent être faites manuellement via l'interface GitHub:

### 1. Configuration des colonnes Kanban

Aller sur le projet → Configurer les colonnes:

- **Backlog** - Issues en attente de priorisation
- **À faire** - Issues priorisées pour le prochain sprint
- **En cours** - Issues en cours de développement
- **En revue** - Pull requests en cours de revue
- **Terminé** - Issues et PRs terminés

### 2. Ajout des Iterations (Sprints)

1. Dans le projet, aller dans **Settings** → **Fields**
2. Créer un nouveau field de type **Iteration**
3. Ajouter les sprints:
   - **Mars 2026** (1er mars 2026, durée: 4 semaines)
   - **Avril 2026** (1er avril 2026, durée: 4 semaines)
   - **Mai 2026** (1er mai 2026, durée: 4 semaines)

### 3. Création des vues personnalisées

#### Vue Kanban (par défaut)
- Type: Board
- Groupé par: Status/Colonne

#### Vue Roadmap
- Type: Roadmap
- Layout: Timeline (3 mois)
- Groupé par: Iteration

#### Vue par dépôt
- Type: Table
- Groupé par: Repository

#### Vue par priorité
- Type: Table
- Groupé par: Labels (priority:*)

## 🏷️ Labels créés

### Priorité
- `priority:high` 🔴 - Priorité haute
- `priority:medium` 🟡 - Priorité moyenne
- `priority:low` 🟢 - Priorité basse

### Type
- `bug` 🐛 - Bug ou erreur à corriger
- `enhancement` ✨ - Nouvelle fonctionnalité ou amélioration
- `documentation` 📚 - Documentation
- `testing` 🧪 - Tests

### Compatibilité
- `prestashop-9` 🛒 - Compatible PrestaShop 9.x
- `php-8.x` 🐘 - Compatible PHP 8.x

### Workflow
- `need-feedback` 💬 - Besoin de retour/clarification
- `ready-to-dev` ✅ - Prêt pour le développement

## 📂 Fichiers

- **`project-config.json`** - Configuration complète du projet (colonnes, iterations, labels, repos)
- **`setup-project.sh`** - Script bash d'installation automatique
- **`README.md`** - Cette documentation
- **`manual-setup-guide.md`** - Guide détaillé pour la configuration manuelle complète

## 🔧 Configuration avancée

### Modification de la configuration

Éditez le fichier `project-config.json` pour:
- Ajouter/supprimer des dépôts
- Modifier les issues à inclure
- Personnaliser les labels
- Ajuster les iterations

### Ajout manuel d'issues au projet

```bash
# Ajouter une issue spécifique
gh project item-add <PROJECT_ID> \
  --owner nenes25 \
  --url https://github.com/nenes25/REPO_NAME/issues/ISSUE_NUM
```

### Création de labels dans un dépôt

```bash
gh label create "label-name" \
  --repo nenes25/REPO_NAME \
  --color "HEXCOLOR" \
  --description "Description du label"
```

## 📚 Ressources

- [Documentation GitHub Projects](https://docs.github.com/fr/issues/planning-and-tracking-with-projects)
- [GitHub CLI Documentation](https://cli.github.com/manual/)
- [Guide des labels GitHub](https://docs.github.com/en/issues/using-labels-and-milestones-to-track-work/managing-labels)

## 🤝 Contribution

Pour proposer des améliorations:
1. Modifier le fichier `project-config.json`
2. Tester avec `./setup-project.sh`
3. Soumettre une Pull Request

## 📞 Support

En cas de problème:
1. Vérifier que GitHub CLI est authentifié: `gh auth status`
2. Vérifier les permissions sur les dépôts
3. Consulter les logs d'erreur du script
4. Ouvrir une issue sur le dépôt hhmodulesmanager

---

**Note**: Ce setup est conçu pour faciliter la gestion collaborative des modules PrestaShop. N'hésitez pas à adapter la configuration selon vos besoins spécifiques.
