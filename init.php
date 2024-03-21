<?php
# Spackle singleton
require __DIR__ . '/lib/Spackle.php';

# Resources
require __DIR__ . '/lib/Customer.php';
require __DIR__ . '/lib/PricingTable.php';

# Stores
require __DIR__ . '/lib/Stores/ApiStore.php';
require __DIR__ . '/lib/Stores/FileStore.php';
require __DIR__ . '/lib/Stores/MemoryStore.php';

# Waiters
require __DIR__ . '/lib/Waiters.php';

# Exceptions
require __DIR__ . '/lib/SpackleException.php';
?>
