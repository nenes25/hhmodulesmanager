# GitHub Project Dashboard - PrestaShop Modules

Ce dossier contient tous les outils et la documentation pour créer et gérer un **GitHub Project global** pour les modules PrestaShop.

## 📍 Localisation

Les fichiers de setup se trouvent dans: `.github/project-setup/`

## 🚀 Démarrage rapide

```bash
cd .github/project-setup

# Option 1: Setup automatique (recommandé)
./setup-project.sh

# Option 2: Voir l'aide
./project-helper.sh help

# Option 3: Lister les dépôts configurés
./project-helper.sh list-repos
```

## 📚 Documentation disponible

| Fichier | Description |
|---------|-------------|
| **QUICKSTART.md** | Guide de démarrage rapide (5 minutes) |
| **README.md** | Documentation complète avec toutes les informations |
| **manual-setup-guide.md** | Guide détaillé pour la configuration manuelle |
| **project-config.json** | Configuration du projet (colonnes, labels, repos, etc.) |
| **setup-project.sh** | Script bash d'installation automatique |
| **project-helper.sh** | Script utilitaire pour les opérations courantes |

## 🎯 Objectif

Créer un dashboard GitHub Project pour gérer:
- 6 modules PrestaShop (eicaptcha, prestashop_console, hhpsmigrationupgradedb, hhmodulesmanager, cronjobs, hhmodulescatalogapi)
- Vue Kanban avec 5 colonnes (Backlog, À faire, En cours, En revue, Terminé)
- Sprints mensuels (Mars, Avril, Mai 2026)
- Labels standardisés (priorité, type, compatibilité, workflow)
- Vues personnalisées (Roadmap, par dépôt, par priorité)

## ⚙️ Prérequis

- GitHub CLI (`gh`) installé et authentifié
- `jq` pour le traitement JSON
- Permissions sur les dépôts concernés

## 🔗 Ressources

- [Documentation GitHub Projects](https://docs.github.com/fr/issues/planning-and-tracking-with-projects)
- [Issue de référence #XXX](../../issues/)

---

**Pour plus de détails, consultez `.github/project-setup/README.md`**
