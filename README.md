# What is this?

A simple project I made during an assessment for a work position. 
As I update this README, in may 2021, I am still working on improvements as a mean of studying.

## Setting up the environment

Notice: This README is for work environments with Linux distros or Windows Subsystem for Linux 2 (WSL2). 
That being said, you will need three tools installed: git, docker and docker-compose.

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

After the startup succeded, set the connection string for the database. Open the .env file inside the /app/ folder, scroll to the botton of the file and comment the line that says "DATABASEURL=postgres(...)", then uncomment the line with "DATABASEURL=mysql" and change:

- db_user to 'root'; 
- password to '123.456';
- address to 'mysql';
- db_name to 'symfony_bank_db'.

Now, create a new clean database, then execute some so-called migrations to set up the application model. 
From this step and on, it's half-way automated, just type in:

To create the database:
```bash
docker-compose exec php php bin/console doctrine:database:create
```

To create the application model:
```bash
docker-compose exec php php bin/console doctrine:migrations:migrate
```

This should be enough. Now, open your browser and the application should be available at the address <http://localhost:8080>
