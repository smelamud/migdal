# @(#) $Id$

$configPath='conf/migdal.conf';
%Config=();

sub readConfig
{
open CONF,$configPath;
while(<CONF>)
     {
     next if /\?>/ || /<\?/;
     s/#.*$//;
     next if /^\s*$/;
     my ($name,$value)=/^\$(\w+)=(.*);\s*$/;
     $value=~s/^\'//;
     $value=~s/\'$//;
     $value=~s/\\\'/\'/;
     $Config{$name}=$value;
     }
close CONF;
}

1;
