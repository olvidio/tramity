--
-- PostgreSQL database dump
--

-- Dumped from database version 10.12 (Ubuntu 10.12-0ubuntu0.18.04.1)
-- Dumped by pg_dump version 10.12 (Ubuntu 10.12-0ubuntu0.18.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: x_locales; Type: TABLE; Schema: public; Owner: tramity
--

CREATE TABLE public.x_locales (
    id_locale character varying(12) NOT NULL,
    nom_locale text,
    idioma character varying(3),
    nom_idioma text,
    activo boolean DEFAULT false NOT NULL
);


ALTER TABLE public.x_locales OWNER TO tramity;

--
-- Data for Name: x_locales; Type: TABLE DATA; Schema: public; Owner: tramity
--

COPY public.x_locales (id_locale, nom_locale, idioma, nom_idioma, activo) FROM stdin;
de_DE.UTF-8	German_Germany	de	German	t
af_ZA.UTF-8	Afrikaans_South Africa	af	Afrikaans	f
sq_AL.UTF-8	Albanian_Albania	sq	Albanian	f
ar_SA.UTF-8	Arabic_Saudi Arabia	ar	Arabic	f
be_BY.UTF-8	Belarusian_Belarus	be	Belarusian	f
bs_BA.UTF-8	Serbian (Latin)	bs	Bosnian	f
bg_BG.UTF-8	Bulgarian_Bulgaria	bg	Bulgarian	f
hr_HR.UTF-8	Croatian_Croatia	hr	Croatian	f
zh_CN.UTF-8	Chinese_China	zh	Chinese (Simplified)	f
zh_TW.UTF-8	Chinese_Taiwan	zh	Chinese (Traditional)	f
da_DK.UTF-8	Danish_Denmark	da	Danish	f
nl_NL.UTF-8	Dutch_Netherlands	nl	Dutch	f
fa_IR.UTF-8	Farsi_Iran	fa	Farsi	f
ph_PH.UTF-8	Filipino_Philippines	fil	Filipino	f
fr_FR.UTF-8	French_France	fr	French	f
ka_GE.UTF-8	Georgian_Georgia	ka	Georgian	f
el_GR.UTF-8	Greek_Greece	el	Greek	f
gu.UTF-8	Gujarati_India	gu	Gujarati	f
he_IL.UTF-8	Hebrew_Israel	he	Hebrew	f
hi_IN.UTF-8	Hindi	hi	Hindi	f
is_IS.UTF-8	Icelandic_Iceland	is	Icelandic	f
id_ID.UTF-8	Indonesian_indonesia	id	Indonesian	f
ja_JP.UTF-8	Japanese_Japan	ja	Japanese	f
kn_IN.UTF-8	Kannada	kn	Kannada	f
km_KH.UTF-8	Khmer	km	Khmer	f
ko_KR.UTF-8	Korean_Korea	ko	Korean	f
lo_LA.UTF-8	Lao_Laos	lo	Lao	f
lt_LT.UTF-8	Lithuanian_Lithuania	lt	Lithuanian	f
lat.UTF-8	Latvian_Latvia	lv	Latvian	f
ml_IN.UTF-8	Malayalam_India	ml	Malayalam	f
ms_MY.UTF-8	Malay_malaysia	ms	Malaysian	f
mn.UTF-8	Cyrillic_Mongolian	mn	Mongolian	f
no_NO.UTF-8	Norwegian_Norway	no	Norwegian	f
nn_NO.UTF-8	Norwegian-Nynorsk_Norway	nn	Nynorsk	f
pl.UTF-8	Polish_Poland	pl	Polish	f
pt_PT.UTF-8	Portuguese_Portugal	pt	Portuguese	f
pt_BR.UTF-8	Portuguese_Brazil	pt	Portuguese (Brazil)	f
ro_RO.UTF-8	Romanian_Romania	ro	Romanian	f
ru_RU.UTF-8	Russian_Russia	ru	Russian	f
mi_NZ.UTF-8	Maori	sm	Samoan	f
sr_CS.UTF-8	Serbian (Cyrillic)_Serbia and Montenegro	sr	Serbian	f
sk_SK.UTF-8	Slovak_Slovakia	sk	Slovak	f
sl_SI.UTF-8	Slovenian_Slovenia	sl	Slovenian	f
sv_SE.UTF-8	Swedish_Sweden	sv	Swedish	f
ta_IN.UTF-8	English_Australia	ta	Tamil	f
th_TH.UTF-8	Thai_Thailand	th	Thai	f
tr_TR.UTF-8	Turkish_Turkey	tr	Turkish	f
uk_UA.UTF-8	Ukrainian_Ukraine	uk	Ukrainian	f
vi_VN.UTF-8	Vietnamese_Viet Nam	vi	Vietnamese	f
ca_ES.UTF-8	Catalan_Spain	ca	Catalan	t
es_ES.UTF-8	Spanish_Spain	es	Spanish	t
hu.UTF-8	Hungarian_Hungary	hu	Hungarian	f
cs_CZ.UTF-8	Czech_Czech Republic	cs	Czech	f
it_IT.UTF-8	Italian_Italy	it	Italian	t
en_US.UTF-8	English_US	en	English	t
en.UTF-8	English_Australia	en	English	f
et_EE.UTF-8	Estonian_Estonia	et	Estonian	f
eu_ES.UTF-8	Basque_Spain	eu	Basque	f
fi_FI.UTF-8	Finnish_Finland	fi	Finnish	f
fr_CA.UTF-8	French_Canada	fr	French	f
gl_ES.UTF-8	Galician_Spain	gl	Gallego	f
\.


--
-- Name: x_locales xidiomas_pkey; Type: CONSTRAINT; Schema: public; Owner: tramity
--

ALTER TABLE ONLY public.x_locales
    ADD CONSTRAINT xidiomas_pkey PRIMARY KEY (id_locale);


--
-- PostgreSQL database dump complete
--

