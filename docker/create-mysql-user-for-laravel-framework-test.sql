CREATE USER 'forge'@'%' IDENTIFIED WITH mysql_native_password BY '';
GRANT ALL PRIVILEGES ON *.* TO 'forge'@'%' WITH GRANT OPTION;
