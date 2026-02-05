#!/bin/bash

# Script utilitaire pour gérer le GitHub Project PrestaShop Modules
# Fournit des commandes pratiques pour les opérations courantes

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="$SCRIPT_DIR/project-config.json"
OWNER="nenes25"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

# Fonction d'aide
show_help() {
    echo -e "${GREEN}=== GitHub Project Helper - PrestaShop Modules ===${NC}\n"
    echo "Usage: $0 <commande> [arguments]"
    echo ""
    echo "Commandes disponibles:"
    echo ""
    echo "  ${BLUE}list-projects${NC}"
    echo "    Liste tous vos projets GitHub"
    echo ""
    echo "  ${BLUE}add-issue${NC} <owner/repo> <issue_number> <project_id>"
    echo "    Ajoute une issue spécifique au projet"
    echo "    Exemple: $0 add-issue nenes25/eicaptcha 331 PROJECT_ID"
    echo ""
    echo "  ${BLUE}create-label${NC} <owner/repo> <label_name> <color> <description>"
    echo "    Crée un label dans un dépôt"
    echo "    Exemple: $0 create-label nenes25/eicaptcha priority:high d73a4a 'Priorité haute'"
    echo ""
    echo "  ${BLUE}bulk-label${NC} <label_name> <color> <description>"
    echo "    Crée un label dans tous les dépôts du projet"
    echo "    Exemple: $0 bulk-label priority:critical ff0000 'Critique'"
    echo ""
    echo "  ${BLUE}list-repos${NC}"
    echo "    Liste tous les dépôts configurés dans le projet"
    echo ""
    echo "  ${BLUE}check-issues${NC} <owner/repo>"
    echo "    Vérifie l'existence des issues configurées pour un dépôt"
    echo "    Exemple: $0 check-issues nenes25/eicaptcha"
    echo ""
    echo "  ${BLUE}help${NC}"
    echo "    Affiche cette aide"
    echo ""
}

# Vérifier les prérequis
check_requirements() {
    if ! command -v gh &> /dev/null; then
        echo -e "${RED}Erreur: GitHub CLI (gh) n'est pas installé${NC}"
        exit 1
    fi
    
    if ! command -v jq &> /dev/null; then
        echo -e "${RED}Erreur: jq n'est pas installé${NC}"
        exit 1
    fi
    
    if ! gh auth status &> /dev/null; then
        echo -e "${RED}Erreur: GitHub CLI n'est pas authentifié${NC}"
        echo "Exécutez: gh auth login"
        exit 1
    fi
}

# Liste les projets
list_projects() {
    echo -e "${YELLOW}Liste des projets GitHub pour @$OWNER:${NC}\n"
    gh project list --owner "$OWNER" --format json | jq -r '.projects[] | "\(.number). \(.title) (ID: \(.id))"'
}

# Ajoute une issue au projet
add_issue() {
    local repo=$1
    local issue_num=$2
    local project_id=$3
    
    if [ -z "$repo" ] || [ -z "$issue_num" ] || [ -z "$project_id" ]; then
        echo -e "${RED}Usage: $0 add-issue <owner/repo> <issue_number> <project_id>${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}Ajout de l'issue #$issue_num du dépôt $repo au projet...${NC}"
    
    gh project item-add "$project_id" \
        --owner "$OWNER" \
        --url "https://github.com/$repo/issues/$issue_num"
    
    echo -e "${GREEN}✓ Issue ajoutée avec succès${NC}"
}

# Crée un label dans un dépôt
create_label() {
    local repo=$1
    local label_name=$2
    local color=$3
    local description=$4
    
    if [ -z "$repo" ] || [ -z "$label_name" ] || [ -z "$color" ]; then
        echo -e "${RED}Usage: $0 create-label <owner/repo> <label_name> <color> <description>${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}Création du label '$label_name' dans $repo...${NC}"
    
    # Vérifier si le label existe
    if gh label list --repo "$repo" --search "$label_name" --limit 1 | grep -q "$label_name"; then
        echo -e "${YELLOW}Le label existe déjà${NC}"
        exit 0
    fi
    
    gh label create "$label_name" \
        --repo "$repo" \
        --color "$color" \
        --description "$description"
    
    echo -e "${GREEN}✓ Label créé avec succès${NC}"
}

# Crée un label dans tous les dépôts
bulk_label() {
    local label_name=$1
    local color=$2
    local description=$3
    
    if [ -z "$label_name" ] || [ -z "$color" ]; then
        echo -e "${RED}Usage: $0 bulk-label <label_name> <color> <description>${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}Création du label '$label_name' dans tous les dépôts...${NC}\n"
    
    # Lire les dépôts depuis la config
    REPOS=$(jq -r '.repositories[] | "\(.owner)/\(.name)"' "$CONFIG_FILE")
    
    while IFS= read -r repo; do
        echo -e "${BLUE}→ $repo${NC}"
        
        if gh label list --repo "$repo" --search "$label_name" --limit 1 | grep -q "$label_name"; then
            echo "  Label existe déjà"
        else
            gh label create "$label_name" \
                --repo "$repo" \
                --color "$color" \
                --description "$description" 2>/dev/null && \
                echo -e "  ${GREEN}✓ Créé${NC}" || \
                echo -e "  ${RED}✗ Échec${NC}"
        fi
    done <<< "$REPOS"
    
    echo -e "\n${GREEN}Opération terminée${NC}"
}

# Liste les dépôts configurés
list_repos() {
    echo -e "${YELLOW}Dépôts configurés dans le projet:${NC}\n"
    jq -r '.repositories[] | "- \(.owner)/\(.name) (\(.issues | length) issues configurées)"' "$CONFIG_FILE"
}

# Vérifie l'existence des issues
check_issues() {
    local repo=$1
    
    if [ -z "$repo" ]; then
        echo -e "${RED}Usage: $0 check-issues <owner/repo>${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}Vérification des issues pour $repo:${NC}\n"
    
    # Trouver le repo dans la config
    REPO_NAME=$(echo "$repo" | cut -d'/' -f2)
    ISSUES=$(jq -r ".repositories[] | select(.name==\"$REPO_NAME\") | .issues[]" "$CONFIG_FILE" 2>/dev/null || echo "")
    
    if [ -z "$ISSUES" ]; then
        echo -e "${RED}Aucune issue configurée pour ce dépôt${NC}"
        exit 1
    fi
    
    while IFS= read -r issue_num; do
        if [ -n "$issue_num" ]; then
            # Vérifier si l'issue existe
            if gh issue view "$issue_num" --repo "$repo" &> /dev/null; then
                echo -e "${GREEN}✓${NC} Issue #$issue_num existe"
            else
                echo -e "${RED}✗${NC} Issue #$issue_num n'existe pas ou n'est pas accessible"
            fi
        fi
    done <<< "$ISSUES"
}

# Main
main() {
    if [ $# -eq 0 ]; then
        show_help
        exit 0
    fi
    
    check_requirements
    
    case "$1" in
        list-projects)
            list_projects
            ;;
        add-issue)
            add_issue "$2" "$3" "$4"
            ;;
        create-label)
            create_label "$2" "$3" "$4" "$5"
            ;;
        bulk-label)
            bulk_label "$2" "$3" "$4"
            ;;
        list-repos)
            list_repos
            ;;
        check-issues)
            check_issues "$2"
            ;;
        help|--help|-h)
            show_help
            ;;
        *)
            echo -e "${RED}Commande inconnue: $1${NC}\n"
            show_help
            exit 1
            ;;
    esac
}

main "$@"
