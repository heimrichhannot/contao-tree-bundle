# Contao Tree Bundle

A contao bundle to create a tree structure like an organization chart in the contao backend and output it in the frontend.

## Features

* create hierarchical structures in a page structure like backend module
* output tree structure in the frontend like you want, comes with an content element and output types for list or bootstrap 4 accordions
* easily extendable: create custom tree node types and custom output type

## Usage

### Install

Install with composer or contao manager and update the database afterwards.

    composer require heimrichhannot/contao-tree-bundle

### Setup

1. In Contao backend you find a new backend module tree structures within content section
1. Create a root node and add child nodes as you want
1. To output it in the frontend, create a Tree content element and select the created root.