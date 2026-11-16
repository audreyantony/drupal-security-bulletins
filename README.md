# Drupal Security Bulletins

![CI](https://github.com/audreyantony/drupal-security-bulletins/actions/workflows/ci.yml/badge.svg)

A small Drupal 11 site publishing security bulletins, built as a learning project :)

> Work in progress. All bulletins and products are fictional.

**En bref (FR)** : portail de bulletins de sécurité sous Drupal 11, avec circuit de validation éditorial, liste filtrable et journal d'audit. Projet d'apprentissage en cours de construction.

## Features so far

- Bulletin content type with CVSS score, affected products and vulnerability type
- Editorial workflow: authors submit, reviewers publish
- Filterable listing, RSS feed, clean URLs
- Audit trail and security headers (CSP) through contributed modules

## Requirements

Docker and [DDEV](https://ddev.com).

## Installation

    git clone https://github.com/audreyantony/drupal-security-bulletins.git
    cd drupal-security-bulletins
    ddev start
    ddev composer install
    ddev drush site:install --existing-config -y
    ddev launch

## License

GPL-2.0-or-later