alter table content_versions
add column cross_entries_version integer not null default 0;
alter table html_cache
add column cross_entries_version integer;
