<?php

declare(strict_types=1);

function dd(mixed $value): void 
{
  echo "<pre>";
  var_dump($value);
  echo "</pre";
  die(); // Tells PHP to stop loading rest of page. Improves performance.
}