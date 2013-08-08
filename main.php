<?php

include 'api.php';

$api = new BlueRoverApi("424/tEh21R7iWlqPSYHmvfszTIFOX7ev8cxmmfHtb75Awf8OUaVo0K6qZ62hmHaN", 
						"bpjYNcPNJUJknjn9NJY/ySREAU5VWcE3bxQYJOow", 
						'http://developers.polairus.com');

$result = $api -> event(1375971038, 1375971238, 0);

echo $result;
?>