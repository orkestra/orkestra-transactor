Contributing Guidelines
=======================

This document details the process you should follow when contributing code to this project.

Overview
--------

The master branch contains active development. It should be considered unstable. Versions are tagged in the repository.


Step by step
------------

### 1. Fork the repository

Fork the repository on GitHub.

### 2. Clone the repository and checkout the master branch

``` bash
git clone git@github.com/<your username>/orkestra-transactor
cd orkestra-transactor
git checkout master
```

### 3. Create a new feature branch.

> Name the branch whatever you like, preferably something descriptive but still short.

``` bash
git checkout -b feature/new-and-awesome
```

### 4. Implement the feature, publish your feature branch to your forked repository

``` bash
git add .
git commit -m "Made some changes"
git push origin feature/new-and-awesome
```

### 5. Create a pull request from your feature branch to the master branch of this project.

From your fork of the repository on GitHub, click the Pull Request button. Select your feature branch and ensure the `master` branch of orkestra-transactor is selected.



Additional Info
---------------

This project tries to avoid BC breaks between minor and patch versions, but some minor caveats apply. See [UPGRADE.md](UPGRADE.md) for details.
