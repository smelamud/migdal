#!/usr/bin/perl

use CGI qw/:standard/;

$cmd=param('cmd');
open OUT,'>>/home/migdal/www/log';
$time=localtime;
print OUT "$time\t$cmd\n";
close OUT;
print header,
      `$cmd`;
