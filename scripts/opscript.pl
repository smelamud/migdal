# @(#) $Id$

sub osSubVar
{
my ($s,$vars,$params)=@_;

return $params->{lc $1}
       if $s=~/^%{?(\w+)}?$/;
return $vars->{''}->[0]->{$1}
       if $s=~/^\${?(?:\w+\.)?(\w+)}?$/;
return $vars->{lc $1}->[0]->{$2}
       if $s=~/^\${?(\w+):(?:\w+\.)?(\w+)}?$/;
return $s;
}

sub osSubVars
{
my ($s,$vars,$params)=@_;
my $c='';

while($s ne '')
     {
     my $head,$var;
     ($head,$var,$s)=$s=~m/
                           ^([^\$%]*)
			   (
			    (?:
			     (?: \${? (?:\w+:)? (?:\w+\.)? \w+ }? )
			     |
			     (?: %{? \w+ }? )
			    )?
			   )
			   (.*)$
			  /x;
     $c.=$head;
     $c.=osSubVar($var,$vars,$params) if $var ne '';
     if($head eq '' && $var eq '')
       {
       $c.=substr($s,0,1);
       substr($s,0,1)='';
       }
     }
return $c;
}

sub opScript
{
my ($dbh,$script,$params)=@_;

my $vars={};
my $result='';

my @script=();
if(ref($script))
  {
  @script=@{$script};
  }
else
  {
  @script=split "\n",$script;
  }
  
my $cont=0;
my $preamble=1;
foreach(@script)
       {
       chomp;
       if($cont)
	 {
	 $s=~s/\\\s*$/$_/;
	 }
       else
	 {
	 $s=$_;
	 }
       $cont=1,next if $s=~/\\\s*$/;
       $cont=0;
       next if $preamble && ($s=~/^\s*$/ || $s=~/^@/);
       $s=osSubVars($s,$vars,$params);
       if($s=~/^\s*\w*\s*=/)
	 {
	 my ($name,$sql)=$s=~/^\s*(\w*)\s*=(.*)$/;
	 my $sth=$dbh->prepare($sql);
	 $sth->execute;
	 $vars->{lc $name}=$sth->fetchall_arrayref({});
	 }
       else
	 {
	 $preamble=0;
	 $result.=$s."\n";
	 }
       }
return $result;
}

1;
