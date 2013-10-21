node default {
  # Core requirements
  include git

  phpenv::php { "5.4.14": }

  phpenv::extension { "PHPUnit":
    extension => "PHPUnit",
    channel   => "pear.phpunit.de",
    require   => Exec["set 5.4.14 as global"]
  }
}
