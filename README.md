# SPAF

Simple PHP API Framework

# Enviroment

You can define a key-value file .env, all the variables there are loaded into the environment using `putenv`.

Then they can be accessed with `getenv` or thoug the custom function `env(variable_name, default_value)`. 

.env example:

```
environment=development
db_host=localhost
db_name=mydb
db_user=admin
db_pass=admin
```