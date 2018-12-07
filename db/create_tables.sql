--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.8
-- Dumped by pg_dump version 9.6.8

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
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


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: chat_messages; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.chat_messages (
    id bigint NOT NULL,
    guest_login character varying(30) NOT NULL,
    sender_id bigint,
    sent timestamp with time zone NOT NULL,
    text character varying(255) DEFAULT ''::character varying NOT NULL,
    text_xml character varying(255) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.chat_messages OWNER TO migdal;

--
-- Name: content_versions; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.content_versions (
    postings_version integer NOT NULL,
    comments_version integer NOT NULL,
    topics_version integer NOT NULL,
    id bigint NOT NULL
);


ALTER TABLE public.content_versions OWNER TO migdal;

--
-- Name: cross_entries; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.cross_entries (
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


ALTER TABLE public.cross_entries OWNER TO migdal;

--
-- Name: entries; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.entries (
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
    comments integer DEFAULT 0 NOT NULL,
    last_comment timestamp with time zone,
    last_comment_id bigint,
    last_comment_user_id bigint,
    last_comment_guest_login character varying(30) NOT NULL,
    small_image bigint,
    small_image_x smallint DEFAULT '0'::smallint NOT NULL,
    small_image_y smallint DEFAULT '0'::smallint NOT NULL,
    small_image_format character varying(30) NOT NULL,
    large_image bigint,
    large_image_x smallint DEFAULT '0'::smallint NOT NULL,
    large_image_y smallint DEFAULT '0'::smallint NOT NULL,
    large_image_size bigint DEFAULT '0'::bigint NOT NULL,
    large_image_format character varying(30) DEFAULT ''::character varying NOT NULL,
    large_image_filename character varying(70) DEFAULT ''::character varying NOT NULL,
    counter0 integer DEFAULT 0 NOT NULL,
    counter1 integer DEFAULT 0 NOT NULL,
    counter2 integer DEFAULT 0 NOT NULL,
    counter3 integer DEFAULT 0 NOT NULL,
    ratio double precision DEFAULT 0 NOT NULL
);


ALTER TABLE public.entries OWNER TO migdal;

--
-- Name: groups; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.groups (
    user_id bigint DEFAULT '0'::bigint NOT NULL,
    group_id bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE public.groups OWNER TO migdal;

--
-- Name: hibernate_sequence; Type: SEQUENCE; Schema: public; Owner: migdal
--

CREATE SEQUENCE public.hibernate_sequence
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.hibernate_sequence OWNER TO migdal;

--
-- Name: html_cache; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.html_cache (
    ident character varying(255) NOT NULL,
    content text NOT NULL,
    deadline timestamp with time zone,
    postings_version integer,
    comments_version integer,
    topics_version integer
);


ALTER TABLE public.html_cache OWNER TO migdal;

--
-- Name: image_file_transforms; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.image_file_transforms (
    id bigint NOT NULL,
    dest_id bigint NOT NULL,
    orig_id bigint NOT NULL,
    transform smallint NOT NULL,
    size_x smallint NOT NULL,
    size_y smallint NOT NULL
);


ALTER TABLE public.image_file_transforms OWNER TO migdal;

--
-- Name: image_files; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.image_files (
    id bigint NOT NULL,
    mime_type character varying(30) NOT NULL,
    size_x smallint NOT NULL,
    size_y smallint NOT NULL,
    file_size bigint NOT NULL,
    created timestamp with time zone,
    accessed timestamp with time zone
);


ALTER TABLE public.image_files OWNER TO migdal;

--
-- Name: inner_images; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.inner_images (
    entry_id bigint DEFAULT '0'::bigint NOT NULL,
    paragraph integer NOT NULL,
    x integer NOT NULL,
    y integer NOT NULL,
    image_id bigint DEFAULT '0'::bigint NOT NULL,
    placement smallint DEFAULT '0'::smallint NOT NULL,
    id bigint NOT NULL
);


ALTER TABLE public.inner_images OWNER TO migdal;

--
-- Name: old_ids; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.old_ids (
    table_name character varying(32) DEFAULT ''::character varying NOT NULL,
    old_id bigint DEFAULT '0'::bigint NOT NULL,
    old_ident character varying(75),
    entry_id bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE public.old_ids OWNER TO migdal;

--
-- Name: prisoners; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.prisoners (
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


ALTER TABLE public.prisoners OWNER TO migdal;

--
-- Name: schema_history; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.schema_history (
    installed_rank integer NOT NULL,
    version character varying(50),
    description character varying(200) NOT NULL,
    type character varying(20) NOT NULL,
    script character varying(1000) NOT NULL,
    checksum integer,
    installed_by character varying(100) NOT NULL,
    installed_on timestamp without time zone DEFAULT now() NOT NULL,
    execution_time integer NOT NULL,
    success boolean NOT NULL
);


ALTER TABLE public.schema_history OWNER TO migdal;

--
-- Name: spring_session; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.spring_session (
    primary_id character(36) NOT NULL,
    session_id character(36) NOT NULL,
    creation_time bigint NOT NULL,
    last_access_time bigint NOT NULL,
    max_inactive_interval integer NOT NULL,
    expiry_time bigint NOT NULL,
    principal_name character varying(100)
);


ALTER TABLE public.spring_session OWNER TO migdal;

--
-- Name: spring_session_attributes; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.spring_session_attributes (
    session_primary_id character(36) NOT NULL,
    attribute_name character varying(200) NOT NULL,
    attribute_bytes bytea NOT NULL
);


ALTER TABLE public.spring_session_attributes OWNER TO migdal;

--
-- Name: users; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.users (
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


ALTER TABLE public.users OWNER TO migdal;

--
-- Name: votes; Type: TABLE; Schema: public; Owner: migdal
--

CREATE TABLE public.votes (
    entry_id bigint,
    ip character varying(40),
    user_id bigint,
    expires timestamp with time zone NOT NULL,
    vote integer NOT NULL,
    id bigint NOT NULL,
    vote_type smallint NOT NULL
);


ALTER TABLE public.votes OWNER TO migdal;

--
-- Name: chat_messages chat_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_pkey PRIMARY KEY (id);


--
-- Name: content_versions content_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.content_versions
    ADD CONSTRAINT content_versions_pkey PRIMARY KEY (id);


--
-- Name: entries entries_ident_key; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_ident_key UNIQUE (ident);


--
-- Name: entries entries_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_pkey PRIMARY KEY (id);


--
-- Name: entries entries_track_key; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_track_key UNIQUE (track);


--
-- Name: html_cache html_cache_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.html_cache
    ADD CONSTRAINT html_cache_pkey PRIMARY KEY (ident);


--
-- Name: image_file_transforms image_file_transforms_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.image_file_transforms
    ADD CONSTRAINT image_file_transforms_pkey PRIMARY KEY (id);


--
-- Name: image_files image_files_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.image_files
    ADD CONSTRAINT image_files_pkey PRIMARY KEY (id);


--
-- Name: inner_images inner_images_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.inner_images
    ADD CONSTRAINT inner_images_pkey PRIMARY KEY (id);


--
-- Name: old_ids old_ids_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.old_ids
    ADD CONSTRAINT old_ids_pkey PRIMARY KEY (table_name, old_id);


--
-- Name: prisoners prisoners_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.prisoners
    ADD CONSTRAINT prisoners_pkey PRIMARY KEY (id);


--
-- Name: schema_history schema_history_pk; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.schema_history
    ADD CONSTRAINT schema_history_pk PRIMARY KEY (installed_rank);


--
-- Name: spring_session_attributes spring_session_attributes_pk; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.spring_session_attributes
    ADD CONSTRAINT spring_session_attributes_pk PRIMARY KEY (session_primary_id, attribute_name);


--
-- Name: spring_session spring_session_pk; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.spring_session
    ADD CONSTRAINT spring_session_pk PRIMARY KEY (primary_id);


--
-- Name: users users_login_key; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_login_key UNIQUE (login);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: votes votes_pkey; Type: CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.votes
    ADD CONSTRAINT votes_pkey PRIMARY KEY (id);


--
-- Name: chat_messages_sender_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX chat_messages_sender_id_idx ON public.chat_messages USING btree (sender_id);


--
-- Name: chat_messages_sent_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX chat_messages_sent_idx ON public.chat_messages USING btree (sent);


--
-- Name: entries_comments_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_comments_idx ON public.entries USING btree (comments);


--
-- Name: entries_current_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_current_id_idx ON public.entries USING btree (current_id);


--
-- Name: entries_disabled_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_disabled_idx ON public.entries USING btree (disabled);


--
-- Name: entries_entry_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_entry_idx ON public.entries USING btree (entry);


--
-- Name: entries_group_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_group_id_idx ON public.entries USING btree (group_id);


--
-- Name: entries_grp_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_grp_idx ON public.entries USING btree (grp);


--
-- Name: entries_index0_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_index0_idx ON public.entries USING btree (index0);


--
-- Name: entries_index1_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_index1_idx ON public.entries USING btree (index1);


--
-- Name: entries_large_image_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_large_image_idx ON public.entries USING btree (large_image);


--
-- Name: entries_last_comment_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_last_comment_idx ON public.entries USING btree (last_comment);


--
-- Name: entries_modbits_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_modbits_idx ON public.entries USING btree (modbits);


--
-- Name: entries_orig_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_orig_id_idx ON public.entries USING btree (orig_id);


--
-- Name: entries_parent_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_parent_id_idx ON public.entries USING btree (parent_id);


--
-- Name: entries_perms_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_perms_idx ON public.entries USING btree (perms);


--
-- Name: entries_person_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_person_id_idx ON public.entries USING btree (person_id);


--
-- Name: entries_priority_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_priority_idx ON public.entries USING btree (priority);


--
-- Name: entries_rating_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_rating_idx ON public.entries USING btree (rating);


--
-- Name: entries_sent_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_sent_idx ON public.entries USING btree (sent);


--
-- Name: entries_small_image_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_small_image_idx ON public.entries USING btree (small_image);


--
-- Name: entries_subject_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_subject_idx ON public.entries USING btree (subject);


--
-- Name: entries_up_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_up_idx ON public.entries USING btree (up);


--
-- Name: entries_url_check_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_url_check_idx ON public.entries USING btree (url_check);


--
-- Name: entries_url_check_success_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_url_check_success_idx ON public.entries USING btree (url_check_success);


--
-- Name: entries_url_domain_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_url_domain_idx ON public.entries USING btree (url_domain);


--
-- Name: entries_user_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entries_user_id_idx ON public.entries USING btree (user_id);


--
-- Name: entry_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX entry_id_idx ON public.votes USING btree (entry_id);


--
-- Name: groups_group_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX groups_group_id_idx ON public.groups USING btree (group_id);


--
-- Name: groups_user_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX groups_user_id_idx ON public.groups USING btree (user_id);


--
-- Name: image_file_transforms_dest_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX image_file_transforms_dest_id_idx ON public.image_file_transforms USING btree (dest_id);


--
-- Name: image_file_transforms_orig_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX image_file_transforms_orig_id_idx ON public.image_file_transforms USING btree (orig_id);


--
-- Name: inner_images_entry_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX inner_images_entry_id_idx ON public.inner_images USING btree (entry_id);


--
-- Name: inner_images_image_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX inner_images_image_id_idx ON public.inner_images USING btree (image_id);


--
-- Name: inner_images_par_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX inner_images_par_idx ON public.inner_images USING btree (paragraph);


--
-- Name: ip_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX ip_idx ON public.votes USING btree (ip);


--
-- Name: old_ids_entry_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX old_ids_entry_id_idx ON public.old_ids USING btree (entry_id);


--
-- Name: old_ids_old_ident_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX old_ids_old_ident_idx ON public.old_ids USING btree (old_ident);


--
-- Name: prisoners_ghetto_name_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX prisoners_ghetto_name_idx ON public.prisoners USING btree (ghetto_name);


--
-- Name: prisoners_location_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX prisoners_location_idx ON public.prisoners USING btree (location);


--
-- Name: prisoners_name_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX prisoners_name_idx ON public.prisoners USING btree (name);


--
-- Name: prisoners_name_russian_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX prisoners_name_russian_idx ON public.prisoners USING btree (name_russian);


--
-- Name: prisoners_sender_name_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX prisoners_sender_name_idx ON public.prisoners USING btree (sender_name);


--
-- Name: schema_history_s_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX schema_history_s_idx ON public.schema_history USING btree (success);


--
-- Name: spring_session_attributes_ix1; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX spring_session_attributes_ix1 ON public.spring_session_attributes USING btree (session_primary_id);


--
-- Name: spring_session_ix1; Type: INDEX; Schema: public; Owner: migdal
--

CREATE UNIQUE INDEX spring_session_ix1 ON public.spring_session USING btree (session_id);


--
-- Name: spring_session_ix2; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX spring_session_ix2 ON public.spring_session USING btree (expiry_time);


--
-- Name: spring_session_ix3; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX spring_session_ix3 ON public.spring_session USING btree (principal_name);


--
-- Name: user_id_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX user_id_idx ON public.votes USING btree (user_id);


--
-- Name: users_confirm_code_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_confirm_code_idx ON public.users USING btree (confirm_code);


--
-- Name: users_confirm_deadline_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_confirm_deadline_idx ON public.users USING btree (confirm_deadline);


--
-- Name: users_guest_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_guest_idx ON public.users USING btree (guest);


--
-- Name: users_jewish_name_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_jewish_name_idx ON public.users USING btree (jewish_name);


--
-- Name: users_login_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_login_idx ON public.users USING btree (login);


--
-- Name: users_name_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_name_idx ON public.users USING btree (name);


--
-- Name: users_password_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_password_idx ON public.users USING btree (password);


--
-- Name: users_shames_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_shames_idx ON public.users USING btree (shames);


--
-- Name: users_surname_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX users_surname_idx ON public.users USING btree (surname);


--
-- Name: vote_type_idx; Type: INDEX; Schema: public; Owner: migdal
--

CREATE INDEX vote_type_idx ON public.votes USING btree (vote_type);


--
-- Name: chat_messages chat_messages_sender_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_sender_id_fkey FOREIGN KEY (sender_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cross_entries cross_entries_peer_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.cross_entries
    ADD CONSTRAINT cross_entries_peer_id_fkey FOREIGN KEY (peer_id) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cross_entries cross_entries_source_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.cross_entries
    ADD CONSTRAINT cross_entries_source_id_fkey FOREIGN KEY (source_id) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_creator_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_creator_id_fkey FOREIGN KEY (creator_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_current_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_current_id_fkey FOREIGN KEY (current_id) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_group_id_fkey FOREIGN KEY (group_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_large_image_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_large_image_fkey FOREIGN KEY (large_image) REFERENCES public.image_files(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_last_comment_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_last_comment_id_fkey FOREIGN KEY (last_comment_id) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_last_comment_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_last_comment_user_id_fkey FOREIGN KEY (last_comment_user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_modifier_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_modifier_id_fkey FOREIGN KEY (modifier_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_orig_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_orig_id_fkey FOREIGN KEY (orig_id) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_person_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_person_id_fkey FOREIGN KEY (person_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_small_image_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_small_image_fkey FOREIGN KEY (small_image) REFERENCES public.image_files(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: entries entries_up_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_up_fkey FOREIGN KEY (up) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entries entries_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.entries
    ADD CONSTRAINT entries_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groups groups_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.groups
    ADD CONSTRAINT groups_group_id_fkey FOREIGN KEY (group_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groups groups_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.groups
    ADD CONSTRAINT groups_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: image_file_transforms image_file_transforms_dest_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.image_file_transforms
    ADD CONSTRAINT image_file_transforms_dest_id_fkey FOREIGN KEY (dest_id) REFERENCES public.image_files(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: image_file_transforms image_file_transforms_orig_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.image_file_transforms
    ADD CONSTRAINT image_file_transforms_orig_id_fkey FOREIGN KEY (orig_id) REFERENCES public.image_files(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: inner_images inner_images_entry_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.inner_images
    ADD CONSTRAINT inner_images_entry_id_fkey FOREIGN KEY (entry_id) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: inner_images inner_images_image_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.inner_images
    ADD CONSTRAINT inner_images_image_id_fkey FOREIGN KEY (image_id) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_ids old_ids_entry_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.old_ids
    ADD CONSTRAINT old_ids_entry_id_fkey FOREIGN KEY (entry_id) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: spring_session_attributes spring_session_attributes_fk; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.spring_session_attributes
    ADD CONSTRAINT spring_session_attributes_fk FOREIGN KEY (session_primary_id) REFERENCES public.spring_session(primary_id) ON DELETE CASCADE;


--
-- Name: votes votes_entry_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.votes
    ADD CONSTRAINT votes_entry_id_fkey FOREIGN KEY (entry_id) REFERENCES public.entries(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: votes votes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: migdal
--

ALTER TABLE ONLY public.votes
    ADD CONSTRAINT votes_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.8
-- Dumped by pg_dump version 9.6.8

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: schema_history; Type: TABLE DATA; Schema: public; Owner: migdal
--

COPY public.schema_history (installed_rank, version, description, type, script, checksum, installed_by, installed_on, execution_time, success) FROM stdin;
1	1	<< Flyway Baseline >>	BASELINE	<< Flyway Baseline >>	\N	migdal	2018-12-05 21:36:32.731025	0	t
2	2	drop large body filename	SQL	V2__drop_large_body_filename.sql	319816289	migdal	2018-12-06 19:53:49.196017	8	t
\.


--
-- PostgreSQL database dump complete
--

