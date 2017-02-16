--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.5
-- Dumped by pg_dump version 9.5.5

SET statement_timeout = 0;
SET lock_timeout = 0;
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

--
-- Name: gender; Type: TYPE; Schema: public; Owner: migdal
--

CREATE TYPE gender AS ENUM (
    'MINE',
    'FEMINE'
);


ALTER TYPE gender OWNER TO migdal;

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
    postings_version bigint NOT NULL,
    forums_version bigint NOT NULL,
    topics_version bigint NOT NULL
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
    link_type bigint DEFAULT '0'::bigint NOT NULL,
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
    up bigint DEFAULT '0'::bigint NOT NULL,
    track character varying(255) DEFAULT ''::character varying NOT NULL,
    catalog character varying(255) DEFAULT ''::character varying NOT NULL,
    parent_id bigint DEFAULT '0'::bigint NOT NULL,
    orig_id bigint DEFAULT '0'::bigint NOT NULL,
    current_id bigint DEFAULT '0'::bigint NOT NULL,
    grp bigint DEFAULT '0'::bigint NOT NULL,
    person_id bigint DEFAULT '0'::bigint NOT NULL,
    guest_login character varying(30) NOT NULL,
    user_id bigint DEFAULT '0'::bigint NOT NULL,
    group_id bigint DEFAULT '0'::bigint NOT NULL,
    perms bigint DEFAULT '0'::bigint NOT NULL,
    disabled smallint DEFAULT '0'::smallint NOT NULL,
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
    has_large_body smallint DEFAULT '0'::smallint NOT NULL,
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
    vote bigint DEFAULT '0'::bigint NOT NULL,
    vote_count bigint DEFAULT '0'::bigint NOT NULL,
    rating double precision DEFAULT '0'::double precision NOT NULL,
    tmpid bigint,
    sent timestamp with time zone,
    created timestamp with time zone,
    modified timestamp with time zone,
    accessed timestamp with time zone,
    creator_id bigint DEFAULT '0'::bigint NOT NULL,
    modifier_id bigint DEFAULT '0'::bigint NOT NULL,
    modbits bigint DEFAULT '0'::bigint NOT NULL,
    answers bigint DEFAULT '0'::bigint NOT NULL,
    last_answer timestamp with time zone,
    last_answer_id bigint DEFAULT '0'::bigint NOT NULL,
    last_answer_user_id bigint DEFAULT '0'::bigint NOT NULL,
    last_answer_guest_login character varying(30) NOT NULL,
    small_image bigint DEFAULT '0'::bigint NOT NULL,
    small_image_x smallint DEFAULT '0'::smallint NOT NULL,
    small_image_y smallint DEFAULT '0'::smallint NOT NULL,
    small_image_format character varying(30) NOT NULL,
    large_image bigint DEFAULT '0'::bigint NOT NULL,
    large_image_x smallint DEFAULT '0'::smallint NOT NULL,
    large_image_y smallint DEFAULT '0'::smallint NOT NULL,
    large_image_size bigint DEFAULT '0'::bigint NOT NULL,
    large_image_format character varying(30) DEFAULT ''::character varying NOT NULL,
    large_image_filename character varying(70) DEFAULT ''::character varying NOT NULL,
    used smallint DEFAULT '0'::smallint NOT NULL
);


ALTER TABLE entries OWNER TO migdal;

--
-- Name: entry_grps; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE entry_grps (
    entry_id bigint DEFAULT '0'::bigint NOT NULL,
    grp bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE entry_grps OWNER TO migdal;

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
    postings_version bigint,
    forums_version bigint,
    topics_version bigint
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
    size_x bigint NOT NULL,
    size_y bigint NOT NULL
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
    created timestamp with time zone NOT NULL,
    accessed timestamp with time zone NOT NULL
);


ALTER TABLE image_files OWNER TO migdal;

--
-- Name: inner_images; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE inner_images (
    entry_id bigint DEFAULT '0'::bigint NOT NULL,
    par bigint DEFAULT '0'::bigint NOT NULL,
    x smallint DEFAULT '0'::smallint NOT NULL,
    y smallint DEFAULT '0'::smallint NOT NULL,
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
-- Name: mail_log; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE mail_log (
    id bigint NOT NULL,
    sent timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE mail_log OWNER TO migdal;

--
-- Name: mail_queue; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE mail_queue (
    id bigint NOT NULL,
    created timestamp with time zone DEFAULT now() NOT NULL,
    destination character varying(255) DEFAULT ''::character varying NOT NULL,
    subject character varying(255) DEFAULT ''::character varying NOT NULL,
    headers text NOT NULL,
    body text NOT NULL
);


ALTER TABLE mail_queue OWNER TO migdal;

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
    type bigint DEFAULT '0'::bigint NOT NULL,
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
    sum bigint NOT NULL,
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
    gender gender DEFAULT 'MINE'::gender NOT NULL,
    info text NOT NULL,
    info_xml text NOT NULL,
    birthday date NOT NULL,
    created timestamp with time zone,
    modified timestamp with time zone,
    last_online timestamp with time zone,
    confirm_deadline timestamp with time zone,
    confirm_code character varying(20) DEFAULT ''::character varying NOT NULL,
    email character varying(70) DEFAULT ''::character varying NOT NULL,
    hide_email smallint DEFAULT '0'::smallint NOT NULL,
    icq character varying(15) DEFAULT ''::character varying NOT NULL,
    email_disabled smallint DEFAULT '0'::smallint NOT NULL,
    shames smallint DEFAULT '0'::smallint NOT NULL,
    guest smallint DEFAULT '0'::smallint NOT NULL,
    rights bigint DEFAULT '0'::bigint NOT NULL,
    hidden smallint DEFAULT '0'::smallint NOT NULL,
    no_login smallint DEFAULT '0'::smallint NOT NULL,
    has_personal smallint DEFAULT '0'::smallint NOT NULL,
    settings character varying(70) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE users OWNER TO migdal;

--
-- Name: version; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE version (
    db_version bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE version OWNER TO migdal;

--
-- Name: votes; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE votes (
    entry_id bigint DEFAULT '0'::bigint NOT NULL,
    ip bigint DEFAULT '0'::bigint NOT NULL,
    user_id bigint DEFAULT '0'::bigint NOT NULL,
    sent timestamp with time zone DEFAULT now() NOT NULL,
    vote bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE votes OWNER TO migdal;

--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


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
-- PostgreSQL database dump complete
--

