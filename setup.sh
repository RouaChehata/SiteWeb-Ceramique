#!/bin/bash

# Vérifier si XAMPP est installé
if [ ! -d "C:/xampp" ]; then
    echo "Erreur : XAMPP n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

# Créer le dossier du projet dans htdocs
mkdir -p "C:/xampp/htdocs/ceramicc"

# Vérifier si la création du dossier a réussi
if [ $? -ne 0 ]; then
    echo "Erreur : Impossible de créer le dossier du projet."
    exit 1
fi

# Copier les fichiers dans le dossier (un par un pour éviter les erreurs)
for ext in php html css js sql; do
    if ls *.$ext 1> /dev/null 2>&1; then
        cp *.$ext "C:/xampp/htdocs/ceramicc/"
    fi
done

# Démarrer XAMPP si ce n'est pas déjà fait
"C:/xampp/xampp_start.exe"

# Vérifier si la base de données existe déjà
if [ -f "create_database.sql" ]; then
    "C:/xampp/mysql/bin/mysql" -u root < create_database.sql
    if [ $? -ne 0 ]; then
        echo "Attention : Erreur lors de l'importation de la base de données."
    fi
else
    echo "Attention : Fichier create_database.sql non trouvé."
fi

echo "Installation terminée !"
echo "Vous pouvez maintenant accéder à votre site via :"  http://localhost/ceramicc/register.php