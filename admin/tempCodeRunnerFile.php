<?php
$query = "UPDATE candidates 
         SET name = '$name', 
             position = '$position', 
             election_id = '$election_id',
             course_id = '$course_id',
             bio = '$bio'
         WHERE id = $candidate_id";