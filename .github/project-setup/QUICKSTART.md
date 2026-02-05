# Quick Start - GitHub Project Setup

Guide de démarrage rapide pour créer le dashboard PrestaShop Modules en 5 minutes.

## 🚀 Option 1: Setup automatique (Recommandé)

### 1. Prérequis (2 minutes)

```bash
# Installer GitHub CLI si nécessaire
# Ubuntu/Debian
sudo apt install gh

# macOS
brew install gh

# Windows
winget install GitHub.cli

# S'authentifier
gh auth login
```

### 2. Exécution du script (1 minute)

```bash
# Cloner le repo si nécessaire
git clone https://github.com/nenes25/hhmodulesmanager.git
cd hhmodulesmanager/.github/project-setup

# Rendre le script exécutable
chmod +x setup-project.sh

# Lancer le setup
./setup-project.sh
```

### 3. Suivre les prompts

Le script vous demandera:
- ✅ Créer le projet? (y/n)
- ✅ Créer les labels? (y/n)
- ✅ Ajouter les issues? (y/n)

### 4. Configuration manuelle finale (2 minutes)

Après le script, configurez manuellement:

1. **Colonnes Kanban** (via l'interface GitHub)
   - Renommer les colonnes: Backlog, À faire, En cours, En revue, Terminé

2. **Iterations/Sprints** (Settings → Fields)
   - Créer field "Iteration"
   - Ajouter Mars, Avril, Mai 2026

3. **Vues personnalisées**
   - Roadmap (timeline)
   - Par dépôt
   - Par priorité

✅ **Terminé! Votre dashboard est opérationnel.**

---

## 📋 Option 2: Setup manuel (10-15 minutes)

Suivez le guide détaillé: [manual-setup-guide.md](./manual-setup-guide.md)

---

## 🔧 Commandes utiles

### Lister vos projets
```bash
./project-helper.sh list-projects
```

### Ajouter une issue au projet
```bash
./project-helper.sh add-issue nenes25/eicaptcha 331 PROJECT_ID
```

### Créer un label dans tous les repos
```bash
./project-helper.sh bulk-label priority:urgent ff0000 "Urgent"
```

### Vérifier les issues d'un repo
```bash
./project-helper.sh check-issues nenes25/eicaptcha
```

---

## 📚 Documentation complète

- **README.md** - Vue d'ensemble et installation
- **manual-setup-guide.md** - Guide manuel étape par étape
- **project-config.json** - Configuration du projet

---

## ⚠️ Dépannage

### Erreur "gh not found"
→ Installez GitHub CLI: https://cli.github.com/

### Erreur "not authenticated"
→ Exécutez: `gh auth login`

### Erreur "jq not found"
→ Installez jq: `sudo apt install jq` ou `brew install jq`

### Permission denied
→ Vérifiez les permissions sur les repos avec: `gh repo view REPO --json viewerPermission`

---

## ✨ Prochaines étapes

1. Organiser les issues dans les colonnes
2. Attribuer les sprints (Mars 2026 pour les priorités)
3. Ajouter les assignees
4. Créer les vues personnalisées
5. Inviter les collaborateurs au projet

---

**Besoin d'aide?** Consultez le [README.md](./README.md) ou le [manual-setup-guide.md](./manual-setup-guide.md)
