<?php
require("Source/Database/PDODatabase.php");
require("Source/Database/DatabasePersistenceService.php");
require("Source/User/Client.php");


$database_file = file("mock.db");
$database = new PDODatabase($database_file);
$persistence_service = new DatabasePersistenceService($database);

$client_1 = new Client("Client name", "Client surname", "+48 123 456 789", "first@client.com");
$client_2 = new Client("Another user", "Another surname", "+11 999 333 666", "second@client.com");

$persistence_service->insert($client_1);
$persistence_service->insert($client_2);

$client_1->setEmail("updated.email@client.com");
$client_1->setPhone("+47 123 456 789");
$persistence_service->update($client_1);

$client_2->setName("Updated name");
$persistence_service->update($client_2);

$persistence_service->remove($client_1);
$persistence_service->remove($client_2);
