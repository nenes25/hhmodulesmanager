#!/bin/bash

# Script pour créer et configurer le GitHub Project pour les modules PrestaShop
# Ce script nécessite l'authentification GitHub CLI (gh)

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="$SCRIPT_DIR/project-config.json"

# Couleurs pour l'affichage
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Setup GitHub Project pour PrestaShop Modules ===${NC}\n"

# Vérifier si gh est installé
if ! command -v gh &> /dev/null; then
    echo -e "${RED}Erreur: GitHub CLI (gh) n'est pas installé${NC}"
    echo "Installez-le depuis: https://cli.github.com/"
    exit 1
fi

# Vérifier si jq est installé
if ! command -v jq &> /dev/null; then
    echo -e "${RED}Erreur: jq n'est pas installé${NC}"
    echo "Installez-le avec: sudo apt-get install jq (Ubuntu/Debian) ou brew install jq (macOS)"
    exit 1
fi

# Vérifier l'authentification
if ! gh auth status &> /dev/null; then
    echo -e "${YELLOW}Vous devez vous authentifier avec GitHub CLI${NC}"
    gh auth login
fi

# Lire la configuration
if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}Erreur: Fichier de configuration non trouvé: $CONFIG_FILE${NC}"
    exit 1
fi

PROJECT_NAME=$(jq -r '.project.name' "$CONFIG_FILE")
PROJECT_DESC=$(jq -r '.project.description' "$CONFIG_FILE")
OWNER="nenes25"

echo -e "${GREEN}Configuration chargée:${NC}"
echo "  - Nom du projet: $PROJECT_NAME"
echo "  - Propriétaire: $OWNER"
echo ""

# Fonction pour créer le projet
create_project() {
    echo -e "${YELLOW}Création du projet GitHub...${NC}"
    
    # Créer le projet (organisation ou utilisateur)
    PROJECT_ID=$(gh project create \
        --owner "$OWNER" \
        --title "$PROJECT_NAME" \
        --format json | jq -r '.id')
    
    if [ -z "$PROJECT_ID" ] || [ "$PROJECT_ID" == "null" ]; then
        echo -e "${RED}Erreur: Impossible de créer le projet${NC}"
        exit 1
    fi
    
    echo -e "${GREEN}✓ Projet créé avec succès (ID: $PROJECT_ID)${NC}"
    echo "$PROJECT_ID"
}

# Fonction pour créer les labels dans tous les repos
create_labels() {
    echo -e "\n${YELLOW}Création des labels dans les dépôts...${NC}"
    
    # Récupérer la liste des dépôts
    REPOS=$(jq -r '.repositories[] | "\(.owner)/\(.name)"' "$CONFIG_FILE")
    
    # Types de labels
    for label_type in priority type compatibility workflow; do
        echo -e "\n${YELLOW}Création des labels de type: $label_type${NC}"
        
        # Lire les labels de ce type
        LABELS=$(jq -c ".labels.$label_type[]" "$CONFIG_FILE")
        
        while IFS= read -r label; do
            LABEL_NAME=$(echo "$label" | jq -r '.name')
            LABEL_COLOR=$(echo "$label" | jq -r '.color')
            LABEL_DESC=$(echo "$label" | jq -r '.description')
            
            echo "  - Création du label: $LABEL_NAME"
            
            # Créer le label dans chaque repo
            while IFS= read -r repo; do
                # Vérifier si le label existe déjà
                if gh label list --repo "$repo" --search "$LABEL_NAME" --limit 1 | grep -q "$LABEL_NAME"; then
                    echo "    → Label '$LABEL_NAME' existe déjà dans $repo"
                else
                    gh label create "$LABEL_NAME" \
                        --repo "$repo" \
                        --color "$LABEL_COLOR" \
                        --description "$LABEL_DESC" 2>/dev/null || echo "    → Échec création dans $repo"
                fi
            done <<< "$REPOS"
            
        done <<< "$LABELS"
    done
    
    echo -e "${GREEN}✓ Labels créés${NC}"
}

# Fonction pour ajouter les issues au projet
add_issues_to_project() {
    local project_id=$1
    
    echo -e "\n${YELLOW}Ajout des issues au projet...${NC}"
    
    # Lire les repositories et leurs issues
    REPO_COUNT=$(jq '.repositories | length' "$CONFIG_FILE")
    
    for ((i=0; i<$REPO_COUNT; i++)); do
        REPO_OWNER=$(jq -r ".repositories[$i].owner" "$CONFIG_FILE")
        REPO_NAME=$(jq -r ".repositories[$i].name" "$CONFIG_FILE")
        REPO_FULL="$REPO_OWNER/$REPO_NAME"
        
        echo -e "\n${YELLOW}Traitement du dépôt: $REPO_FULL${NC}"
        
        # Lire les numéros d'issues
        ISSUES=$(jq -r ".repositories[$i].issues[]" "$CONFIG_FILE" 2>/dev/null || echo "")
        
        if [ -z "$ISSUES" ]; then
            echo "  → Aucune issue spécifiée pour ce dépôt"
            continue
        fi
        
        while IFS= read -r issue_num; do
            if [ -n "$issue_num" ]; then
                echo "  - Ajout de l'issue #$issue_num..."
                
                # Ajouter l'issue au projet
                gh project item-add "$project_id" \
                    --owner "$OWNER" \
                    --url "https://github.com/$REPO_FULL/issues/$issue_num" 2>/dev/null || \
                    echo "    → Échec ajout issue #$issue_num (peut-être déjà ajoutée ou inexistante)"
            fi
        done <<< "$ISSUES"
    done
    
    echo -e "${GREEN}✓ Issues ajoutées au projet${NC}"
}

# Afficher les instructions pour les étapes manuelles
show_manual_steps() {
    local project_id=$1
    
    echo -e "\n${YELLOW}=== Étapes manuelles restantes ===${NC}"
    echo ""
    echo "Le projet a été créé avec succès. Certaines configurations doivent être faites manuellement:"
    echo ""
    echo "1. Configurer les colonnes du tableau Kanban:"
    echo "   - Aller sur: https://github.com/users/$OWNER/projects"
    echo "   - Ouvrir le projet '$PROJECT_NAME'"
    echo "   - Ajouter/renommer les colonnes: Backlog, À faire, En cours, En revue, Terminé"
    echo ""
    echo "2. Ajouter les iterations (sprints):"
    echo "   - Dans le projet, aller dans Settings → Fields"
    echo "   - Créer un nouveau field de type 'Iteration'"
    echo "   - Ajouter les sprints:"
    echo "     * Mars 2026 (1er mars - durée: 4 semaines)"
    echo "     * Avril 2026 (1er avril - durée: 4 semaines)"
    echo "     * Mai 2026 (1er mai - durée: 4 semaines)"
    echo ""
    echo "3. Créer les vues personnalisées:"
    echo "   - Vue Kanban (par colonne/status)"
    echo "   - Vue Roadmap (timeline, groupé par Iteration)"
    echo "   - Vue par dépôt (groupé par repository)"
    echo "   - Vue par priorité (groupé par labels)"
    echo ""
    echo "4. Configurer la visibilité du projet (Settings → Visibility)"
    echo ""
    echo -e "${GREEN}Projet créé avec ID: $project_id${NC}"
    echo -e "${GREEN}URL du projet: https://github.com/users/$OWNER/projects${NC}"
}

# Main
main() {
    echo -e "${YELLOW}Voulez-vous procéder à la création du projet? (y/n)${NC}"
    read -r response
    
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        echo "Annulé."
        exit 0
    fi
    
    # Créer le projet
    PROJECT_ID=$(create_project)
    
    # Créer les labels
    echo -e "\n${YELLOW}Voulez-vous créer les labels dans tous les dépôts? (y/n)${NC}"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        create_labels
    fi
    
    # Ajouter les issues
    echo -e "\n${YELLOW}Voulez-vous ajouter les issues au projet? (y/n)${NC}"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        add_issues_to_project "$PROJECT_ID"
    fi
    
    # Afficher les instructions pour les étapes manuelles
    show_manual_steps "$PROJECT_ID"
    
    echo -e "\n${GREEN}=== Setup terminé avec succès! ===${NC}"
}

# Exécuter le script principal
main
