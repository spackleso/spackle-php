<?php
# Spackle singleton
require __DIR__ . '/lib/Spackle.php';

# Resources
require __DIR__ . '/lib/Customer.php';

# Stores
require __DIR__ . '/lib/Stores/MemoryStore.php';
require __DIR__ . '/lib/Stores/DynamoDBStore.php';

# Exceptions
require __DIR__ . '/lib/SpackleException.php';
?>
