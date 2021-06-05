# RewriteNoSubCategories
## plg_system_rewritenosubcategories

--------------------------------------

English

--------------------------------------

## WHY ?
This project start because of a challenge a joomler friend of mine posted in JUG Breizh on FB on February 16th 2021
The challenge was:

Given an url structure like:

/parent-category/subcat-a/article-1
/parent-category/subcat-a/article-2
/parent-category/subcat-c/article-7

How to make it work with urls like:

/parent-category/article-1
/parent-category/article-2
/parent-category/article-7

In other words without the subcategory

## WHAT ?
The answer to that delicious puzzle was custom router rules.

## HOW ?
TLDR; Custom router rules injected via a system plugin onAfterInitialise event just right after Joomla! started "booting"

## USAGE
The Joomla! extension is in the build folder of this repository or if you prefer to build it yourself, execute the following commands in your Terminal:

```

git clone https://github.com/alexandreelise/plg_system_rewritenosubcategories.git

cd plg_system_rewritenosubcategories

make

```

Tested on Ubuntu 20.04 LTS but should work also on macOS and Windows 10 WSL 2

## CONTRIBUTIONS:
You can improve this project and add your contributions to help the other "Super Joomlers" like you.

------------------------------------------

Français

------------------------------------------

## POURQUOI ?
Ce projet a commencé grâce à un challenge qu'un ami joomler a posté sur le JUG Breizh sur FB le 16 Février 2021.
Voici le challenge:

Pour une url ayant la structure suivante:

/categorie-parente/sous-cat-a/article-1
/categorie-parente/sous-cat-a/article-2
/categorie-parente/sous-cat-c/article-7

Comment faire pour que les urls fonctionnent aussi sous cette forme:

/categorie-parente/article-1
/categorie-parente/article-2
/categorie-parente/article-7

En d'autres mots sans les sous-catégories.

## QUOI ?
La réponse à ce délicieux puzzle est de créér des règles de routeur personnalisées.

## COMMENT ?
TLDR; Des règles de routeur personnalisées injectées via un plugin système lors de l'événement onAfterInitilise juste après que Joomla! est "démarré"

## UTILISATION
L'extension Joomla! est dans le dossier build de ce dépot.
Si vous préférez construire vous meme l'extension, exécutez ces commandes dans votre Terminal.

```

git clone https://github.com/alexandreelise/plg_system_rewritenosubcategories.git

cd plg_system_rewritenosubcategories

make

```

Testé sur Ubuntu 20.04 LTS mais devrait fonctionner aussi sous macOS et Windows 10 WSL 2

## CONTRIBUTIONS:
Vous pouvez améliorer ce projet et apporter vos contributions pour faire avancer la communauté des "Super Joomlers" comme vous.

--------------------------------------------
## INFOS

> English: [Click here to get in touch](https://github.com/mralexandrelise/mralexandrelise/blob/master/community.md "Get in touch")

> Français: [Cliquez ici pour me contacter](https://github.com/mralexandrelise/mralexandrelise/blob/master/community.md "Me contacter")
