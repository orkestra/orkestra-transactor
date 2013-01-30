Contributing
============

This document details the process you should follow when contributing code.

Overview
--------

The master branch contains active development. It should be considered unstable. Versions are tagged in the repository.


Step by step
------------

### 1. Fork the repository

Fork the repository on GitHub.

### 2. Clone the repository and checkout the master branch

``` bash
git clone https://github.com/orkestra/orkestra-transactor
cd orkestra-transactor
git checkout master
```

### 3. Create a new feature branch. In contributors case, a bug fix is still accomplished from a feature branch.

``` bash
git checkout -b feature/new-and-awesome
```

### 4. Implement the feature, publish (push) your feature branch to your forked repository

``` bash
git add .
git commit -m "Made some changes"
git push origin feature/new-and-awesome
```

### 5. Create a pull request from your feature branch to the **master** branch of this project.

From your repository on the GitHub interface, click the pull request button. Select your feature branch and ensure
the master branch of orkestra-transactor is selected.



Additional Info
---------------

This project follows [Semantic Version](http://semver.org) for the most part. See UPGRADE.md for details on backwards
compatibilty breaks.
