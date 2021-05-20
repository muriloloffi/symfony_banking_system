# What is this?

A simple project I made during an assessment for a work position. 
As I update this README, in may 2021, I am still working on improvements as a mean of studying.

## Setting up the environment

This README is written for work environments with any Linux distro or WSL2. 
You will need three tools installed: git, docker and docker-compose.

### Clone this repository; 

For this you can open a terminal in your folder of preference and copy-paste the commands below, all at once. This will pull the code from github and get you to the folder where it was downloaded:

```bash
git clone https://github.com/muriloloffi/symfony_banking_system.git \
&& cd symfony_banking_system
```

Now, start up the environment by exectuing:

```bash
docker-compose up -d
```

After the start up succeded, create a new clean database, then execute some so-called migrations to set up the application model. 
It's half-way automated, just type in:

Creating the database:
```bash
docker-compose exec php php bin/console doctrine:database:create
```

Creating the application model:
```bash
docker-compose exec php php bin/console doctrine:migrations:migrate
```

This should be enough. Now, open your browser and the application should be available at the address <http://localhost:8080>
