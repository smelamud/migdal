--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.5
-- Dumped by pg_dump version 9.6.5

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: captcha_keys; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE captcha_keys (
    id bigint NOT NULL,
    keystring character varying(6) DEFAULT ''::character varying NOT NULL,
    sid character varying(32) DEFAULT ''::character varying NOT NULL,
    created timestamp with time zone
);


ALTER TABLE captcha_keys OWNER TO migdal;

--
-- Name: chat_messages; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE chat_messages (
    id bigint NOT NULL,
    guest_login character varying(30) NOT NULL,
    sender_id bigint DEFAULT '0'::bigint NOT NULL,
    private_id bigint DEFAULT '0'::bigint NOT NULL,
    sent timestamp with time zone DEFAULT now() NOT NULL,
    text character varying(255) DEFAULT ''::character varying NOT NULL,
    text_xml character varying(255) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE chat_messages OWNER TO migdal;

--
-- Name: content_versions; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE content_versions (
    postings_version integer NOT NULL,
    forums_version integer NOT NULL,
    topics_version integer NOT NULL
);


ALTER TABLE content_versions OWNER TO migdal;

--
-- Name: counters; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE counters (
    id bigint NOT NULL,
    entry_id bigint DEFAULT '0'::bigint NOT NULL,
    mode bigint DEFAULT '0'::bigint NOT NULL,
    serial bigint DEFAULT '0'::bigint NOT NULL,
    value bigint DEFAULT '0'::bigint NOT NULL,
    started timestamp with time zone,
    finished timestamp with time zone,
    used smallint DEFAULT '0'::smallint NOT NULL
);


ALTER TABLE counters OWNER TO migdal;

--
-- Name: counters_ip; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE counters_ip (
    counter_id bigint DEFAULT '0'::bigint NOT NULL,
    ip bigint DEFAULT '0'::bigint NOT NULL,
    expires timestamp with time zone
);


ALTER TABLE counters_ip OWNER TO migdal;

--
-- Name: cross_entries; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE cross_entries (
    id bigint NOT NULL,
    source_name character varying(255),
    source_id bigint,
    link_type integer NOT NULL,
    peer_name character varying(255),
    peer_id bigint,
    peer_path character varying(255) DEFAULT ''::character varying NOT NULL,
    peer_subject character varying(255) NOT NULL,
    peer_icon character varying(64) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE cross_entries OWNER TO migdal;

--
-- Name: entries; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE entries (
    id bigint NOT NULL,
    ident character varying(75),
    entry smallint DEFAULT '0'::smallint NOT NULL,
    up bigint,
    track character varying(255) DEFAULT ''::character varying,
    catalog character varying(255) DEFAULT ''::character varying NOT NULL,
    parent_id bigint,
    orig_id bigint,
    current_id bigint,
    grp bigint DEFAULT '0'::bigint NOT NULL,
    person_id bigint,
    guest_login character varying(30) NOT NULL,
    user_id bigint,
    group_id bigint,
    perms bigint DEFAULT '0'::bigint NOT NULL,
    disabled boolean NOT NULL,
    subject character varying(255) NOT NULL,
    lang character varying(7) DEFAULT ''::character varying NOT NULL,
    author character varying(255) DEFAULT ''::character varying NOT NULL,
    author_xml character varying(255) DEFAULT ''::character varying NOT NULL,
    source character varying(255) DEFAULT ''::character varying NOT NULL,
    source_xml character varying(255) DEFAULT ''::character varying NOT NULL,
    title character varying(255) DEFAULT ''::character varying NOT NULL,
    title_xml character varying(255) DEFAULT ''::character varying NOT NULL,
    comment0 character varying(255) DEFAULT ''::character varying NOT NULL,
    comment0_xml character varying(255) DEFAULT ''::character varying NOT NULL,
    comment1 character varying(255) DEFAULT ''::character varying NOT NULL,
    comment1_xml character varying(255) DEFAULT ''::character varying NOT NULL,
    url character varying(255) DEFAULT ''::character varying NOT NULL,
    url_domain character varying(70) DEFAULT ''::character varying NOT NULL,
    url_check timestamp with time zone,
    url_check_success timestamp with time zone,
    body text NOT NULL,
    body_xml text NOT NULL,
    body_format bigint DEFAULT '0'::bigint NOT NULL,
    has_large_body boolean NOT NULL,
    large_body text NOT NULL,
    large_body_xml text NOT NULL,
    large_body_format bigint DEFAULT '0'::bigint NOT NULL,
    large_body_filename character varying(70) DEFAULT ''::character varying NOT NULL,
    priority smallint DEFAULT '0'::smallint NOT NULL,
    index0 bigint DEFAULT '0'::bigint NOT NULL,
    index1 bigint DEFAULT '0'::bigint NOT NULL,
    index2 bigint DEFAULT '0'::bigint NOT NULL,
    set0 bigint DEFAULT '0'::bigint NOT NULL,
    set0_index bigint DEFAULT '0'::bigint NOT NULL,
    set1 bigint DEFAULT '0'::bigint NOT NULL,
    set1_index bigint DEFAULT '0'::bigint NOT NULL,
    vote integer DEFAULT 0 NOT NULL,
    vote_count integer DEFAULT 0 NOT NULL,
    rating double precision DEFAULT '0'::double precision NOT NULL,
    sent timestamp with time zone,
    created timestamp with time zone,
    modified timestamp with time zone,
    accessed timestamp with time zone,
    creator_id bigint,
    modifier_id bigint,
    modbits bigint DEFAULT '0'::bigint NOT NULL,
    answers integer DEFAULT 0 NOT NULL,
    last_answer timestamp with time zone,
    last_answer_id bigint,
    last_answer_user_id bigint,
    last_answer_guest_login character varying(30) NOT NULL,
    small_image bigint,
    small_image_x smallint DEFAULT '0'::smallint NOT NULL,
    small_image_y smallint DEFAULT '0'::smallint NOT NULL,
    small_image_format character varying(30) NOT NULL,
    large_image bigint,
    large_image_x smallint DEFAULT '0'::smallint NOT NULL,
    large_image_y smallint DEFAULT '0'::smallint NOT NULL,
    large_image_size bigint DEFAULT '0'::bigint NOT NULL,
    large_image_format character varying(30) DEFAULT ''::character varying NOT NULL,
    large_image_filename character varying(70) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE entries OWNER TO migdal;

--
-- Name: groups; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE groups (
    user_id bigint DEFAULT '0'::bigint NOT NULL,
    group_id bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE groups OWNER TO migdal;

--
-- Name: hibernate_sequence; Type: SEQUENCE; Schema: public; Owner: migdal
--

CREATE SEQUENCE hibernate_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE hibernate_sequence OWNER TO migdal;

--
-- Name: html_cache; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE html_cache (
    ident character varying(255) NOT NULL,
    content text NOT NULL,
    deadline timestamp with time zone,
    postings_version integer,
    forums_version integer,
    topics_version integer
);


ALTER TABLE html_cache OWNER TO migdal;

--
-- Name: image_file_transforms; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE image_file_transforms (
    id bigint NOT NULL,
    dest_id bigint NOT NULL,
    orig_id bigint NOT NULL,
    transform smallint NOT NULL,
    size_x smallint NOT NULL,
    size_y smallint NOT NULL
);


ALTER TABLE image_file_transforms OWNER TO migdal;

--
-- Name: image_files; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE image_files (
    id bigint NOT NULL,
    mime_type character varying(30) NOT NULL,
    size_x smallint NOT NULL,
    size_y smallint NOT NULL,
    file_size bigint NOT NULL,
    created timestamp with time zone,
    accessed timestamp with time zone
);


ALTER TABLE image_files OWNER TO migdal;

--
-- Name: inner_images; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE inner_images (
    entry_id bigint DEFAULT '0'::bigint NOT NULL,
    par integer NOT NULL,
    x integer NOT NULL,
    y integer NOT NULL,
    image_id bigint DEFAULT '0'::bigint NOT NULL,
    placement smallint DEFAULT '0'::smallint NOT NULL,
    used smallint DEFAULT '0'::smallint NOT NULL
);


ALTER TABLE inner_images OWNER TO migdal;

--
-- Name: logs; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE logs (
    id bigint NOT NULL,
    event character varying(30) DEFAULT ''::character varying NOT NULL,
    sent timestamp with time zone DEFAULT now() NOT NULL,
    ip bigint DEFAULT '0'::bigint NOT NULL,
    body character varying(250) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE logs OWNER TO migdal;

--
-- Name: old_ids; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE old_ids (
    table_name character varying(32) DEFAULT ''::character varying NOT NULL,
    old_id bigint DEFAULT '0'::bigint NOT NULL,
    old_ident character varying(75),
    entry_id bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE old_ids OWNER TO migdal;

--
-- Name: packages; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE packages (
    id bigint NOT NULL,
    entry_id bigint DEFAULT '0'::bigint NOT NULL,
    type integer NOT NULL,
    mime_type character varying(50) DEFAULT ''::character varying NOT NULL,
    title character varying(250) DEFAULT ''::character varying NOT NULL,
    body bytea NOT NULL,
    size bigint DEFAULT '0'::bigint NOT NULL,
    url character varying(250) DEFAULT ''::character varying NOT NULL,
    created timestamp with time zone,
    used smallint DEFAULT '0'::smallint NOT NULL
);


ALTER TABLE packages OWNER TO migdal;

--
-- Name: prisoners; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE prisoners (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    name_russian character varying(255) NOT NULL,
    gender character(1) NOT NULL,
    age character varying(31) NOT NULL,
    location character varying(255) NOT NULL,
    ghetto_name character varying(255) NOT NULL,
    sender_name character varying(255) NOT NULL,
    sum integer NOT NULL,
    search_data character varying(255) NOT NULL
);


ALTER TABLE prisoners OWNER TO migdal;

--
-- Name: profiling; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE profiling (
    id bigint NOT NULL,
    up bigint DEFAULT '0'::bigint NOT NULL,
    track character varying(255) DEFAULT ''::character varying NOT NULL,
    sent timestamp with time zone DEFAULT now() NOT NULL,
    object smallint DEFAULT '0'::smallint NOT NULL,
    name character varying(250) DEFAULT ''::character varying NOT NULL,
    begin_time bigint DEFAULT '0'::bigint NOT NULL,
    end_time bigint DEFAULT '0'::bigint NOT NULL,
    comment character varying(250) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE profiling OWNER TO migdal;

--
-- Name: redirs; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE redirs (
    id bigint NOT NULL,
    up bigint DEFAULT '0'::bigint NOT NULL,
    track character varying(255) DEFAULT ''::character varying,
    uri text NOT NULL,
    last_access timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE redirs OWNER TO migdal;

--
-- Name: spring_session; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE spring_session (
    primary_id character(36) NOT NULL,
    session_id character(36) NOT NULL,
    creation_time bigint NOT NULL,
    last_access_time bigint NOT NULL,
    max_inactive_interval integer NOT NULL,
    expiry_time bigint NOT NULL,
    principal_name character varying(100)
);


ALTER TABLE spring_session OWNER TO migdal;

--
-- Name: spring_session_attributes; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE spring_session_attributes (
    session_primary_id character(36) NOT NULL,
    attribute_name character varying(200) NOT NULL,
    attribute_bytes bytea NOT NULL
);


ALTER TABLE spring_session_attributes OWNER TO migdal;

--
-- Name: tmp_texts; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE tmp_texts (
    id bigint NOT NULL,
    value text NOT NULL,
    last_access timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE tmp_texts OWNER TO migdal;

--
-- Name: users; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE users (
    id bigint NOT NULL,
    login character varying(30) NOT NULL,
    password character varying(40) DEFAULT ''::character varying NOT NULL,
    name character varying(30) NOT NULL,
    jewish_name character varying(30) NOT NULL,
    surname character varying(30) NOT NULL,
    info text NOT NULL,
    info_xml text NOT NULL,
    created timestamp with time zone,
    modified timestamp with time zone,
    last_online timestamp with time zone,
    confirm_deadline timestamp with time zone,
    confirm_code character varying(20) DEFAULT ''::character varying NOT NULL,
    email character varying(70) DEFAULT ''::character varying NOT NULL,
    hide_email boolean NOT NULL,
    email_disabled smallint NOT NULL,
    shames boolean NOT NULL,
    guest boolean NOT NULL,
    rights bigint DEFAULT '0'::bigint NOT NULL,
    hidden smallint DEFAULT '0'::smallint NOT NULL,
    no_login boolean NOT NULL,
    has_personal boolean NOT NULL,
    settings character varying(70) DEFAULT ''::character varying NOT NULL,
    gender smallint NOT NULL,
    birthday_day smallint NOT NULL,
    birthday_month smallint NOT NULL,
    birthday_year smallint NOT NULL
);


ALTER TABLE users OWNER TO migdal;

--
-- Name: version; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE version (
    db_version integer NOT NULL
);


ALTER TABLE version OWNER TO migdal;

--
-- Name: votes; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE votes (
    entry_id bigint,
    ip inet NOT NULL,
    user_id bigint,
    expires timestamp with time zone NOT NULL,
    vote integer NOT NULL,
    id bigint NOT NULL,
    count_entry_id bigint,
    vote_count integer NOT NULL,
    vote_type smallint NOT NULL
);


ALTER TABLE votes OWNER TO migdal;

--
-- Name: entries entries_ident_key; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_ident_key UNIQUE (ident);


--
-- Name: entries entries_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_pkey PRIMARY KEY (id);


--
-- Name: entries entries_track_key; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_track_key UNIQUE (track);


--
-- Name: image_file_transforms image_file_transforms_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY image_file_transforms
    ADD CONSTRAINT image_file_transforms_pkey PRIMARY KEY (id);


--
-- Name: image_files image_files_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY image_files
    ADD CONSTRAINT image_files_pkey PRIMARY KEY (id);


--
-- Name: spring_session_attributes spring_session_attributes_pk; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY spring_session_attributes
    ADD CONSTRAINT spring_session_attributes_pk PRIMARY KEY (session_primary_id, attribute_name);


--
-- Name: spring_session spring_session_pk; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY spring_session
    ADD CONSTRAINT spring_session_pk PRIMARY KEY (primary_id);


--
-- Name: users users_login_key; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_login_key UNIQUE (login);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: votes votes_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY votes
    ADD CONSTRAINT votes_pkey PRIMARY KEY (id);


--
-- Name: count_entry_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX count_entry_id_idx ON votes USING btree (count_entry_id);


--
-- Name: entries_answers_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_answers_idx ON entries USING btree (answers);


--
-- Name: entries_current_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_current_id_idx ON entries USING btree (current_id);


--
-- Name: entries_disabled_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_disabled_idx ON entries USING btree (disabled);


--
-- Name: entries_entry_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_entry_idx ON entries USING btree (entry);


--
-- Name: entries_group_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_group_id_idx ON entries USING btree (group_id);


--
-- Name: entries_grp_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_grp_idx ON entries USING btree (grp);


--
-- Name: entries_index0_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_index0_idx ON entries USING btree (index0);


--
-- Name: entries_index1_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_index1_idx ON entries USING btree (index1);


--
-- Name: entries_large_image_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_large_image_idx ON entries USING btree (large_image);


--
-- Name: entries_last_answer_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_last_answer_idx ON entries USING btree (last_answer);


--
-- Name: entries_modbits_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_modbits_idx ON entries USING btree (modbits);


--
-- Name: entries_orig_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_orig_id_idx ON entries USING btree (orig_id);


--
-- Name: entries_parent_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_parent_id_idx ON entries USING btree (parent_id);


--
-- Name: entries_perms_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_perms_idx ON entries USING btree (perms);


--
-- Name: entries_person_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_person_id_idx ON entries USING btree (person_id);


--
-- Name: entries_priority_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_priority_idx ON entries USING btree (priority);


--
-- Name: entries_rating_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_rating_idx ON entries USING btree (rating);


--
-- Name: entries_sent_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_sent_idx ON entries USING btree (sent);


--
-- Name: entries_small_image_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_small_image_idx ON entries USING btree (small_image);


--
-- Name: entries_subject_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_subject_idx ON entries USING btree (subject);


--
-- Name: entries_up_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_up_idx ON entries USING btree (up);


--
-- Name: entries_url_check_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_url_check_idx ON entries USING btree (url_check);


--
-- Name: entries_url_check_success_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_url_check_success_idx ON entries USING btree (url_check_success);


--
-- Name: entries_url_domain_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_url_domain_idx ON entries USING btree (url_domain);


--
-- Name: entries_user_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_user_id_idx ON entries USING btree (user_id);


--
-- Name: entry_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entry_id_idx ON votes USING btree (entry_id);


--
-- Name: groups_group_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX groups_group_id_idx ON groups USING btree (group_id);


--
-- Name: groups_user_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX groups_user_id_idx ON groups USING btree (user_id);


--
-- Name: image_file_transforms_dest_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX image_file_transforms_dest_id_idx ON image_file_transforms USING btree (dest_id);


--
-- Name: image_file_transforms_orig_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX image_file_transforms_orig_id_idx ON image_file_transforms USING btree (orig_id);


--
-- Name: spring_session_attributes_ix1; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX spring_session_attributes_ix1 ON spring_session_attributes USING btree (session_primary_id);


--
-- Name: spring_session_ix1; Type: INDEX; Schema: public; Owner: migdal
--

CREATE UNIQUE INDEX spring_session_ix1 ON spring_session USING btree (session_id);


--
-- Name: spring_session_ix2; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX spring_session_ix2 ON spring_session USING btree (expiry_time);


--
-- Name: spring_session_ix3; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX spring_session_ix3 ON spring_session USING btree (principal_name);


--
-- Name: user_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX user_id_idx ON votes USING btree (user_id);


--
-- Name: users_confirm_code_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_confirm_code_idx ON users USING btree (confirm_code);


--
-- Name: users_confirm_deadline_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_confirm_deadline_idx ON users USING btree (confirm_deadline);


--
-- Name: users_guest_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_guest_idx ON users USING btree (guest);


--
-- Name: users_jewish_name_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_jewish_name_idx ON users USING btree (jewish_name);


--
-- Name: users_login_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_login_idx ON users USING btree (login);


--
-- Name: users_name_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_name_idx ON users USING btree (name);


--
-- Name: users_password_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_password_idx ON users USING btree (password);


--
-- Name: users_shames_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_shames_idx ON users USING btree (shames);


--
-- Name: users_surname_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_surname_idx ON users USING btree (surname);


--
-- Name: vote_type_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX vote_type_idx ON votes USING btree (vote_type);


--
-- Name: cross_entries cross_entries_peer_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY cross_entries
    ADD CONSTRAINT cross_entries_peer_id_fkey FOREIGN KEY (peer_id) REFERENCES entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cross_entries cross_entries_source_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY cross_entries
    ADD CONSTRAINT cross_entries_source_id_fkey FOREIGN KEY (source_id) REFERENCES entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_creator_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_creator_id_fkey FOREIGN KEY (creator_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_current_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_current_id_fkey FOREIGN KEY (current_id) REFERENCES entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_group_id_fkey FOREIGN KEY (group_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_large_image_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_large_image_fkey FOREIGN KEY (large_image) REFERENCES image_files(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_last_answer_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_last_answer_id_fkey FOREIGN KEY (last_answer_id) REFERENCES entries(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_last_answer_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_last_answer_user_id_fkey FOREIGN KEY (last_answer_user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_modifier_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_modifier_id_fkey FOREIGN KEY (modifier_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_orig_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_orig_id_fkey FOREIGN KEY (orig_id) REFERENCES entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_person_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_person_id_fkey FOREIGN KEY (person_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_small_image_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_small_image_fkey FOREIGN KEY (small_image) REFERENCES image_files(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_up_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_up_fkey FOREIGN KEY (up) REFERENCES entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY entries
    ADD CONSTRAINT entries_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groups groups_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY groups
    ADD CONSTRAINT groups_group_id_fkey FOREIGN KEY (group_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groups groups_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY groups
    ADD CONSTRAINT groups_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: image_file_transforms image_file_transforms_dest_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY image_file_transforms
    ADD CONSTRAINT image_file_transforms_dest_id_fkey FOREIGN KEY (dest_id) REFERENCES image_files(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: image_file_transforms image_file_transforms_orig_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY image_file_transforms
    ADD CONSTRAINT image_file_transforms_orig_id_fkey FOREIGN KEY (orig_id) REFERENCES image_files(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: spring_session_attributes spring_session_attributes_fk; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY spring_session_attributes
    ADD CONSTRAINT spring_session_attributes_fk FOREIGN KEY (session_primary_id) REFERENCES spring_session(primary_id) ON DELETE CASCADE;


--
-- Name: votes votes_count_entry_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY votes
    ADD CONSTRAINT votes_count_entry_id_fkey FOREIGN KEY (count_entry_id) REFERENCES entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: votes votes_entry_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY votes
    ADD CONSTRAINT votes_entry_id_fkey FOREIGN KEY (entry_id) REFERENCES entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: votes votes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY votes
    ADD CONSTRAINT votes_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

