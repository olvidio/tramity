CREATE TABLE nombre_del_esquema.lugares
(
    id_lugar   SERIAL PRIMARY KEY,
    sigla      text                  NOT NULL,
    dl         character varying(6),
    region     character varying(5),
    nombre     character varying(35),
    tipo_ctr   character varying(5),
    modo_envio smallint,
    plataforma text,
    pub_key    bytea,
    e_mail     text,
    anulado    boolean DEFAULT false NOT NULL,
    autorizacion character varying(255)
);


ALTER TABLE nombre_del_esquema.lugares OWNER TO tramity;


CREATE INDEX sigla_lugares_key ON nombre_del_esquema.lugares USING btree (sigla);

CREATE TABLE nombre_del_esquema.lugares_grupos
(
    id_grupo    SERIAL PRIMARY KEY,
    descripcion text NOT NULL,
    miembros    integer[]
);

ALTER TABLE nombre_del_esquema.lugares_grupos OWNER TO tramity;

--
-- Data for Name: lugares; Type: TABLE DATA; Schema: nombre_del_esquema. Owner: tramity
--

COPY nombre_del_esquema.lugares (id_lugar, sigla, dl, region, nombre, tipo_ctr, modo_envio, pub_key, e_mail, anulado) FROM stdin;
58	agdMontagut	dlb	H	agdMontagut	am	1
\N
\N	f
59	agdNargó	dlb	H	agdNargó	aj	1
\N
\N	f
1	A	-	A	Austria	cr	1
\N
\N	f
205	Viamar	dlb	H	Viamar	nm	1
\N
\N	f
2	Acme	-	Acme	América Central Sur	cr	1
\N
\N	f
3	dlhon	dlhon	Acme	Tegucigalpa	dl	1
\N
\N	f
4	Acse	-	Acse	América Central Norte	cr	1
\N
\N	f
6	Afm	-	Afm	Sudáfrica	cr	1
\N
\N	f
7	Afo	-	Afo	Africa del Este	cr	1
\N
\N	f
8	Arg	-	Arg	Argentina	cr	1
\N
\N	f
9	dlba	dlba	Arg	Buenos Aires	dl	1
\N
\N	f
10	dlBol	dlBol	Arg	La Paz	dl	1
\N
\N	f
11	dlPar	dlPar	Arg	Asunción	dl	1
\N
\N	f
12	dlros	dlros	Arg	Rosario	dl	1
\N
\N	f
13	Asmo	-	Asmo	Sudeste asiático	cr	1
\N
\N	f
14	Aso	-	Aso	Asia del Este	cr	1
\N
\N	f
15	Aut	-	Aut	Australia	cr	1
\N
\N	f
16	B	-	B	Brasil	cr	1
\N
\N	f
17	dlrio	dlrio	B	Río de Janeiro	dl	1
\N
\N	f
18	dlsp	dlsp	B	dlsp	dl	1
\N
\N	f
19	Bal	-	Bal	Países Bálticos	cr	1
\N
\N	f
20	Bel	-	Bel	Bélgica	cr	1
\N
\N	f
22	Brit	-	Brit	Inglaterra	cr	1
\N
\N	f
23	C	-	C	Canadá	cr	1
\N
\N	f
25	Cam	-	Cam	Camerún	cr	1
\N
\N	f
26	Ceb	-	Ceb	Costa de Marfil	cr	1
\N
\N	f
27	crs+	crs+	cg	Roma	dl	1
\N
\N	f
28	Ch	-	Ch	Chile	cr	1
\N
\N	f
30	Col	-	Col	Colombia	cr	1
\N
\N	f
31	Cong	-	Cong	Congo	cr	1
\N
\N	f
32	Cro	-	Cro	Croacia	cr	1
\N
\N	f
33	Csl	-	Csl	Chequia y Eslovaquia	cr	1
\N
\N	f
34	E	-	E	Ecuador	cr	1
\N
\N	f
35	Eu	-	Eu	Estados Unidos	cr	1
\N
\N	f
36	dlc	dlc	Eu	Chicago	dl	1
\N
\N	f
37	dlCal	dlCal	Eu	California	dl	1
\N
\N	f
38	dltx	dltx	Eu	Houston	dl	1
\N
\N	f
39	Fes	-	Fes	Finlandia y Estonia	cr	1
\N
\N	f
40	G	-	G	Alemania	cr	1
\N
\N	f
41	Gal	-	Gal	Francia	cr	1
\N
\N	f
42	H	-	H	España	cr	1
\N
\N	f
44	cr	cr	H	cr	dl	1
\N
\N	f
45	agdAragó	dlb	H	agdAragó	am	1
\N
\N	f
46	agdArinsal	dlb	H	agdArinsal	am	1
\N
\N	f
47	agdAubens	dlb	H	agdAubens	am	1
\N
\N	f
206	Vianya	dlb	H	Vianya	nm	1
\N
\N	f
48	agdBalandrau	dlb	H	agdBalandrau	aj	1
\N
\N	f
49	agdBrafa	dlb	H	agdBrafa	am	1
\N
\N	f
50	agdBraval	dlb	H	agdBraval	aj	1
\N
\N	f
52	agdCiurana	dlb	H	agdCiurana	aj	1
\N
\N	f
55	agdFanals	dlb	H	agdFanals	am	1
\N
\N	f
57	agdLes Corts	dlb	H	agdLes Corts	aj	1
\N
\N	f
62	agdRaset	dlb	H	agdRaset	am	1
\N
\N	f
63	agdTempir	dlb	H	agdTempir	aj	1
\N
\N	f
64	agdTravessera	dlb	H	agdTravessera	am	1
\N
\N	f
66	agdVallès	dlb	H	agdVallès	am	1
\N
\N	f
67	agdViamar	dlb	H	agdViamar	am	1
\N
\N	f
68	agdXaloc	dlb	H	agdXaloc	am	1
\N
\N	f
69	Alimara	dlb	H	Alimara	nm	1
\N
\N	f
70	Antara	dlb	H	Antara	aj	1
\N
\N	f
71	Aragó	dlb	H	Aragó	nm	1
\N
\N	f
72	Arinsal	dlb	H	Arinsal	nm	1
\N
\N	f
74	Bell-lloc	dlb	H	Bell-lloc	cgioc	1
\N
\N	f
75	Bellmunt	dlb	H	Bellmunt	nm	1
\N
\N	f
76	Brafa	dlb	H	Brafa	oc	1
\N
\N	f
78	Calaf	dlb	H	Calaf	nm	1
\N
\N	f
82	Cancillería	dlb	H	Cancillería	cr	1
\N
\N	f
85	cebm (Monterols)	dlb	H	cebm (Monterols)	njce	1
\N
\N	f
86	cesgB	dlb	H	cesgB	smce	1
\N
\N	f
87	cesgRiells	dlb	H	cesgRiells	sjce	1
\N
\N	f
88	cgi Camp Joliu	dlb	H	cgi Camp Joliu	cgi	1
\N
\N	f
89	cgi La Farga	dlb	H	cgi La Farga	cgi	1
\N
\N	f
90	cgi Mestral	dlb	H	cgi Mestral	cgi	1
\N
\N	f
91	cgi Terraferma	dlb	H	cgi Terraferma	cgi	1
\N
\N	f
92	cgi Turó	dlb	H	cgi Turó	cgi	1
\N
\N	f
93	Cimal	dlb	H	Cimal	nj	1
\N
\N	f
97	D'Aran	dlb	H	D'Aran	nj	1
\N
\N	f
98	Daumar	dlb	H	Daumar	nj	1
\N
\N	f
99	Diagonal	dlb	H	Diagonal	nm	1
\N
\N	f
100	dlbf	dlb	H	dlbf	dl	1
\N
\N	f
107	EFA Quintanes	dlb	H	EFA Quintanes	cgi	1
\N
\N	f
108	El Far	dlb	H	El Far	xp	1
\N
\N	f
110	Fabra	dlb	H	Fabra	nm	1
\N
\N	f
111	Fanals	dlb	H	Fanals	nm	1
\N
\N	f
112	FERT	dlb	H	FERT	cgi	1
\N
\N	f
118	IESE	dlb	H	IESE	oc	1
\N
\N	f
119	Les Corts	dlb	H	Les Corts	nm	1
\N
\N	f
120	Les Pedritxes	dlb	H	Les Pedritxes	nm	1
\N
\N	f
121	L'Estudi	dlb	H	L'Estudi	nm	1
\N
\N	f
122	Manresa (sss+)	dlb	H	Manresa (sss+)	xp	1
\N
\N	f
125	Masia	dlb	H	Masia	nm	1
\N
\N	f
126	Moneders	dlb	H	Moneders	nm	1
\N
\N	f
127	Montagut	dlb	H	Montagut	nm	1
\N
\N	f
128	Montalegre	dlb	H	Montalegre	igloc	1
\N
\N	f
129	Montalegre (enfermos)	dlb	H	Montalegre (enfermos)	igl	1
\N
\N	f
131	Montroig	dlb	H	Montroig	nj	1
\N
\N	f
132	of-dlb	dlb	H	of-dlb	of	1
\N
\N	f
133	Oficina d'Informació	dlb	H	Oficina d'Informació	oi	1
\N
\N	f
134	Oratori de Santa Maria	dlb	H	Oratori de Santa Maria	igloc	1
\N
\N	f
135	Oratori de Santa Maria (enfermos)	dlb	H	Oratori de Santa Maria (enfermos)	igl	1
\N
\N	f
136	Pàdua	dlb	H	Pàdua	nj	1
\N
\N	f
137	Palau	dlb	H	Palau	am	1
\N
\N	f
138	Pedralbes	dlb	H	Pedralbes	nj	1
\N
\N	f
140	Puigdoure	dlb	H	Puigdoure	nm	1
\N
\N	f
142	Puigterrà	dlb	H	Puigterrà	xp	1
\N
\N	f
143	Racó-sr	dlb	H	Racó-sr	nj	1
\N
\N	f
144	Raier	dlb	H	Raier	nj	1
\N
\N	f
146	Raset	dlb	H	Raset	nm	1
\N
\N	f
147	Riells	dlb	H	Riells	nm	1
\N
\N	f
149	Sant Francesc	dlb	H	Sant Francesc	igl	1
\N
\N	f
150	Santa Maria de Gràcia	dlb	H	Santa Maria de Gràcia	igl	1
\N
\N	f
151	Sarrià	dlb	H	Sarrià	nm	1
\N
\N	f
152	Segrià	dlb	H	Segrià	nm	1
\N
\N	f
153	sgAragó	dlb	H	sgAragó	sm	1
\N
\N	f
154	sgArinsal	dlb	H	sgArinsal	sm	1
\N
\N	f
156	sgBellmunt	dlb	H	sgBellmunt	sm	1
\N
\N	f
157	sgBrafa	dlb	H	sgBrafa	sm	1
\N
\N	f
158	sgBraval	dlb	H	sgBraval	sm	1
\N
\N	f
159	sgDiagonal	dlb	H	sgDiagonal	sm	1
\N
\N	f
160	sgFanals	dlb	H	sgFanals	sm	1
\N
\N	f
161	sgLes Corts	dlb	H	sgLes Corts	sm	1
\N
\N	f
162	sgLes Pedritxes	dlb	H	sgLes Pedritxes	sm	1
\N
\N	f
163	sgMasia	dlb	H	sgMasia	sm	1
\N
\N	f
164	sgMontagut	dlb	H	sgMontagut	sm	1
\N
\N	f
166	sgPedralbes	dlb	H	sgPedralbes	sj	1
\N
\N	f
167	sgPuigdoure	dlb	H	sgPuigdoure	sm	1
\N
\N	f
168	sgRaset	dlb	H	sgRaset	sm	1
\N
\N	f
169	sgRiells	dlb	H	sgRiells	xx	1
\N
\N	f
170	sgSarrià	dlb	H	sgSarrià	sm	1
\N
\N	f
171	sgSegrià	dlb	H	sgSegrià	sm	1
\N
\N	f
172	sgTerrassa	dlb	H	sgTerrassa	sm	1
\N
\N	f
173	sgTibidabo	dlb	H	sgTibidabo	sm	1
\N
\N	f
174	sgTravessera	dlb	H	sgTravessera	sm	1
\N
\N	f
175	sgTres Torres	dlb	H	sgTres Torres	sm	1
\N
\N	f
176	sgVallclar	dlb	H	sgVallclar	sm	1
\N
\N	f
177	sgValldaura	dlb	H	sgValldaura	sm	1
\N
\N	f
178	sgValldoreix	dlb	H	sgValldoreix	sm	1
\N
\N	f
179	sgVallès	dlb	H	sgVallès	sm	1
\N
\N	f
180	sgViamar	dlb	H	sgViamar	sm	1
\N
\N	f
181	sgVianya	dlb	H	sgVianya	sm	1
\N
\N	f
182	sgViaró	dlb	H	sgViaró	sm	1
\N
\N	f
183	sgViera	dlb	H	sgViera	sj	1
\N
\N	f
184	sgXaloc	dlb	H	sgXaloc	sm	1
\N
\N	f
185	sss+Barcelona	dlb	H	sss+Barcelona	ss	1
\N
\N	f
186	sss+Girona	dlb	H	sss+Girona	ss	1
\N
\N	f
187	sss+Lleida	dlb	H	sss+Lleida	ss	1
\N
\N	f
188	sss+Tarragona	dlb	H	sss+Tarragona	ss	1
\N
\N	f
189	sss+Terrassa	dlb	H	sss+Terrassa	ss	1
\N
\N	f
190	sss+Vic	dlb	H	sss+Vic	ss	1
\N
\N	f
191	Tempir	dlb	H	Tempir	nj	1
\N
\N	f
192	Tibidabo	dlb	H	Tibidabo	nm	1
\N
\N	f
196	Tramuntana	dlb	H	Tramuntana	cl	1
\N
\N	f
197	Travessera	dlb	H	Travessera	nm	1
\N
\N	f
198	Tres Torres	dlb	H	Tres Torres	nm	1
\N
\N	f
199	UIC	dlb	H	UIC	cgi	1
\N
\N	f
200	UIC St. Cugat	dlb	H	UIC St. Cugat	cgi	1
\N
\N	f
201	Valira	dlb	H	Valira	nm	1
\N
\N	f
202	Vallclar	dlb	H	Vallclar	nm	1
\N
\N	f
203	Valldaura-sr	dlb	H	Valldaura-sr	nj	1
\N
\N	f
204	Valldoreix	dlb	H	Valldoreix	nm	1
\N
\N	f
207	Viaró	dlb	H	Viaró	nm	1
\N
\N	f
208	Viaró Misa domingo	dlb	H	Viaró Misa domingo	igl	1
\N
\N	f
209	Viaró oc	dlb	H	Viaró oc	cgioc	1
\N
\N	f
210	Viera	dlb	H	Viera	nj	1
\N
\N	f
212	Xaloc	dlb	H	Xaloc	nm	1
\N
\N	f
213	Xaloc Misa domingo	dlb	H	Xaloc Misa domingo	igl	1
\N
\N	f
214	Xaloc oc	dlb	H	Xaloc oc	cgioc	1
\N
\N	f
215	dlgr	dlgr	H	Granada	dl	1
\N
\N	f
218	dlmE	dlmE	H	Madrid Este	dl	1
\N
\N	f
219	dlmO	dlmO	H	Madrid Oeste	dl	1
\N
\N	f
220	dlp	dlp	H	Pamplona	dl	1
\N
\N	f
221	dls	dls	H	Sevilla	dl	1
\N
\N	f
222	dlst	dlst	H	Santiago	dl	1
\N
\N	f
223	dlv	dlv	H	Valladolid	dl	1
\N
\N	f
224	dlva	dlva	H	Valencia	dl	1
\N
\N	f
225	dlz	dlz	H	Zaragoza	dl	1
\N
\N	f
226	Hel	-	Hel	Suiza	cr	1
\N
\N	f
227	Hiber	-	Hiber	Irlanda	cr	1
\N
\N	f
228	I	-	I	Italia	cr	1
\N
\N	f
229	dep	dep	I	Palermo	dl	1
\N
\N	f
230	dro	dro	I	Roma	dl	1
\N
\N	f
231	Iers	-	Iers	Jerusalén	cr	1
\N
\N	f
232	Ind	-	Ind	India	cr	1
\N
\N	f
233	J	-	J	Japón	cr	1
\N
\N	f
234	Kz	-	Kz	Kazajstán	cr	1
\N
\N	f
235	L	-	L	Portugal 	cr	1
\N
\N	f
236	Li	-	Li	Líbano	cr	1
\N
\N	f
237	M	-	M	México	cr	1
\N
\N	f
238	dlg	dlg	M	Guadalajara	dl	1
\N
\N	f
240	dlj	dlj	M	El Bajío	dl	1
\N
\N	f
241	dlm	dlm	M	México	dl	1
\N
\N	f
242	dly	dly	M	Monterrey	dl	1
\N
\N	f
243	Nig	-	Nig	Nigeria	cr	1
\N
\N	f
244	Nl	-	Nl	Holanda	cr	1
\N
\N	f
245	P	-	P	Perú	cr	1
\N
\N	f
247	Pl	-	Pl	Filipinas	cr	1
\N
\N	f
248	Pol	-	Pol	Polonia	cr	1
\N
\N	f
249	Port	-	Port	Puerto Rico	cr	1
\N
\N	f
250	Rs	-	Rs	Rusia	cr	1
\N
\N	f
252	Sal	-	Sal	El Salvador	cr	1
\N
\N	f
253	Scan	-	Scan	Suecia	cr	1
\N
\N	f
254	Sln	-	Sln	Eslovenia	cr	1
\N
\N	f
256	U	-	U	Uruguay	cr	1
\N
\N	f
257	V	-	V	Venezuela	cr	1
\N
\N	f
263	agdDani	dlb	H	DaniCtr	nm	1
\N
\N	f
73	dlb	dlb	H	dlb - Barcelona	dl	1
\N
\N	f
\.



SELECT pg_catalog.setval('nombre_del_esquema.lugares_id_lugar_seq', 263, true);

