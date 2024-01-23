<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
        xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
        xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0"
        xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
        xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
        xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
        xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
        xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
        xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2"
        xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0"
        xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0"
        xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0"
        xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0"
        xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
        xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
>

    <!-- Copyright (C) 2006 by Tapsell-Ferrier Limited
    This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2, or (at your option) any later version.
    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
    You should have received a copy of the GNU General Public License along with this program; see the file COPYING. If not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
    -->

    <xsl:output method="xml" indent="yes"/>
    <xsl:output encoding="utf-8"/>
    <xsl:template match="/html">
        <office:document-content office:version="1.2">
            <office:scripts/>
            <office:font-face-decls>
                <style:font-face style:name="Calibri" svg:font-family="Calibri" style:font-family-generic="system"
                                 style:font-pitch="variable"/>
                <style:font-face style:name="Droid Sans Fallback" svg:font-family="&apos;Droid Sans Fallback&apos;"
                                 style:font-family-generic="system" style:font-pitch="variable"/>
                <style:font-face style:name="FreeSans" svg:font-family="FreeSans" style:font-family-generic="system"
                                 style:font-pitch="variable"/>
                <style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;"
                                 style:font-family-generic="roman" style:font-pitch="variable"/>
                <style:font-face style:name="Liberation Sans1" svg:font-family="&apos;Liberation Sans&apos;"
                                 style:font-adornments="Regular" style:font-family-generic="swiss"
                                 style:font-pitch="variable"/>
                <style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;"
                                 style:font-family-generic="roman" style:font-pitch="variable"/>
                <style:font-face style:name="Lohit Devanagari" svg:font-family="&apos;Lohit Devanagari&apos;"
                                 style:font-family-generic="system" style:font-pitch="variable"/>
                <style:font-face style:name="OpenSymbol" svg:font-family="OpenSymbol" style:font-charset="x-symbol"/>
                <style:font-face style:name="OpenSymbol1" svg:font-family="OpenSymbol" style:font-family-generic="roman"
                                 style:font-pitch="variable"/>
                <style:font-face style:name="OpenSymbol2" svg:font-family="OpenSymbol"
                                 style:font-family-generic="system" style:font-pitch="variable"/>
                <style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;"
                                 style:font-family-generic="roman" style:font-pitch="variable"/>
                <style:font-face style:name="Times New Roman1" svg:font-family="&apos;Times New Roman&apos;"
                                 style:font-family-generic="system" style:font-pitch="variable"/>
            </office:font-face-decls>
            <office:automatic-styles>
                <style:style style:name="P1" style:family="paragraph" style:parent-style-name="Standard">
                    <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm"
                                                fo:margin-bottom="0.199cm" style:contextual-spacing="false"
                                                fo:text-align="justify" style:justify-single-word="false"
                                                fo:hyphenation-ladder-count="no-limit" fo:text-indent="1.501cm"
                                                style:auto-text-indent="false"/>
                    <style:text-properties fo:hyphenate="true" fo:hyphenation-remain-char-count="2"
                                           fo:hyphenation-push-char-count="2" loext:hyphenation-no-caps="false"/>
                </style:style>
                <style:style style:name="PsaltoPagina" style:family="paragraph" style:parent-style-name="Sandard">
                    <style:paragraph-properties fo:break-before="page"/>
                </style:style>

                <style:style style:name="P2" style:family="paragraph" style:parent-style-name="Standard">
                    <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:margin-top="0cm"
                                                fo:margin-bottom="1cm" fo:text-align="center"
                                                style:justify-single-word="false" fo:orphans="2" fo:widows="2"
                                                fo:hyphenation-ladder-count="no-limit" fo:text-indent="0cm"
                                                style:auto-text-indent="false"/>
                    <style:text-properties fo:font-size="13pt" fo:language="es" fo:country="ES"
                                           style:letter-kerning="false" style:font-size-asian="13pt"
                                           style:language-asian="en" style:country-asian="US"
                                           style:font-size-complex="13pt" style:language-complex="ar"
                                           style:country-complex="SA" fo:hyphenate="true"
                                           fo:hyphenation-remain-char-count="2" fo:hyphenation-push-char-count="2"
                                           loext:hyphenation-no-caps="false"/>
                </style:style>

                <style:style style:name="P3" style:family="paragraph" style:parent-style-name="Standard">
                    <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-align="end"
                                                style:justify-single-word="false" fo:orphans="2" fo:widows="2"
                                                fo:hyphenation-ladder-count="no-limit" fo:text-indent="1.501cm"
                                                style:auto-text-indent="false"/>
                    <style:text-properties fo:font-size="13pt" fo:language="es" fo:country="ES"
                                           style:letter-kerning="false" style:font-size-asian="13pt"
                                           style:language-asian="en" style:country-asian="US"
                                           style:font-size-complex="13pt" style:language-complex="ar"
                                           style:country-complex="SA" fo:hyphenate="true"
                                           fo:hyphenation-remain-char-count="2" fo:hyphenation-push-char-count="2"
                                           loext:hyphenation-no-caps="false"/>
                </style:style>
                <style:style style:name="P4" style:family="paragraph" style:parent-style-name="subApartado"
                             style:list-style-name="L1"/>
                <style:style style:name="P5" style:family="paragraph" style:parent-style-name="subApartado"
                             style:list-style-name="L1">
                    <style:text-properties officeooo:paragraph-rsid="002191fd"/>
                </style:style>
                <style:style style:name="P6" style:family="paragraph" style:parent-style-name="subApartado"
                             style:list-style-name="L2">
                    <style:text-properties officeooo:paragraph-rsid="00228687"/>
                </style:style>
                <style:style style:name="T1" style:family="text">
                    <style:text-properties fo:font-size="13pt" style:font-size-asian="13pt"
                                           style:font-size-complex="13pt"/>
                </style:style>
                <style:style style:name="T2" style:family="text">
                    <style:text-properties fo:font-size="13pt" fo:language="es" fo:country="ES"
                                           style:letter-kerning="false" style:font-size-asian="13pt"
                                           style:language-asian="en" style:country-asian="US"
                                           style:font-size-complex="13pt" style:language-complex="ar"
                                           style:country-complex="SA"/>
                </style:style>
                <style:style style:name="T3" style:family="text">
                    <style:text-properties fo:language="es" fo:country="ES" fo:font-weight="bold"
                                           style:language-asian="en" style:country-asian="US"
                                           style:font-weight-asian="bold" style:language-complex="ar"
                                           style:country-complex="SA" style:font-weight-complex="bold"/>
                </style:style>

                <style:style style:name="T4" style:family="text">
                    <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold"
                                           style:font-weight-complex="bold"/>
                </style:style>
                <style:style style:name="T5" style:family="text">
                    <style:text-properties style:text-underline-style="solid" style:text-underline-width="auto"
                                           style:text-underline-color="font-color"/>
                </style:style>
                <style:style style:name="T6" style:family="text">
                    <style:text-properties fo:font-style="italic" style:font-style-asian="italic"
                                           style:font-style-complex="italic"/>
                </style:style>

                <text:list-style style:name="L1">
                    <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols"
                                                  loext:num-list-format="%1%." style:num-suffix="."
                                                  text:bullet-char="•">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="3.27cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="3.27cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-bullet>
                    <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols"
                                                  loext:num-list-format="%2%." style:num-suffix="."
                                                  text:bullet-char="◦">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="3.905cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="3.905cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-bullet>
                    <text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols"
                                                  loext:num-list-format="%3%." style:num-suffix="."
                                                  text:bullet-char="▪">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="4.54cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="4.54cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-bullet>
                    <text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols"
                                                  loext:num-list-format="%4%." style:num-suffix="."
                                                  text:bullet-char="•">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="5.175cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="5.175cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-bullet>
                    <text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols"
                                                  loext:num-list-format="%5%." style:num-suffix="."
                                                  text:bullet-char="◦">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="5.81cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="5.81cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-bullet>
                    <text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols"
                                                  loext:num-list-format="%6%." style:num-suffix="."
                                                  text:bullet-char="▪">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="6.445cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="6.445cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-bullet>
                    <text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols"
                                                  loext:num-list-format="%7%." style:num-suffix="."
                                                  text:bullet-char="•">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="7.08cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="7.08cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-bullet>
                    <text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols"
                                                  loext:num-list-format="%8%." style:num-suffix="."
                                                  text:bullet-char="◦">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="7.715cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="7.715cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-bullet>
                    <text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols"
                                                  loext:num-list-format="%9%." style:num-suffix="."
                                                  text:bullet-char="▪">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="8.35cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="8.35cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-bullet>
                    <text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols"
                                                  loext:num-list-format="%10%." style:num-suffix="."
                                                  text:bullet-char="•">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="8.985cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="8.985cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-bullet>
                </text:list-style>
                <text:list-style style:name="L2">
                    <text:list-level-style-number text:level="1" text:style-name="Numbering_20_Symbols"
                                                  loext:num-list-format="%1%)" style:num-suffix=")"
                                                  style:num-format="1">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="3.27cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="3.27cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-number>
                    <text:list-level-style-number text:level="2" text:style-name="Numbering_20_Symbols"
                                                  loext:num-list-format="%2%." style:num-suffix="."
                                                  style:num-format="a">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="3.905cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="3.905cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-number>
                    <text:list-level-style-number text:level="3" text:style-name="Numbering_20_Symbols"
                                                  loext:num-list-format="%3%." style:num-suffix="."
                                                  style:num-format="1">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="4.54cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="4.54cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-number>
                    <text:list-level-style-number text:level="4" text:style-name="Numbering_20_Symbols"
                                                  loext:num-list-format="%4%." style:num-suffix="."
                                                  style:num-format="1">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="5.175cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="5.175cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-number>
                    <text:list-level-style-number text:level="5" text:style-name="Numbering_20_Symbols"
                                                  loext:num-list-format="%5%." style:num-suffix="."
                                                  style:num-format="1">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="5.81cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="5.81cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-number>
                    <text:list-level-style-number text:level="6" text:style-name="Numbering_20_Symbols"
                                                  loext:num-list-format="%6%." style:num-suffix="."
                                                  style:num-format="1">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="6.445cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="6.445cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-number>
                    <text:list-level-style-number text:level="7" text:style-name="Numbering_20_Symbols"
                                                  loext:num-list-format="%7%." style:num-suffix="."
                                                  style:num-format="1">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="7.08cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="7.08cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-number>
                    <text:list-level-style-number text:level="8" text:style-name="Numbering_20_Symbols"
                                                  loext:num-list-format="%8%." style:num-suffix="."
                                                  style:num-format="1">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="7.715cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="7.715cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-number>
                    <text:list-level-style-number text:level="9" text:style-name="Numbering_20_Symbols"
                                                  loext:num-list-format="%9%." style:num-suffix="."
                                                  style:num-format="1">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="8.35cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="8.35cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-number>
                    <text:list-level-style-number text:level="10" text:style-name="Numbering_20_Symbols"
                                                  loext:num-list-format="%10%." style:num-suffix="."
                                                  style:num-format="1">
                        <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
                            <style:list-level-label-alignment text:label-followed-by="listtab"
                                                              text:list-tab-stop-position="8.985cm"
                                                              fo:text-indent="-0.635cm" fo:margin-left="8.985cm"/>
                        </style:list-level-properties>
                    </text:list-level-style-number>
                </text:list-style>
            </office:automatic-styles>
            <xsl:apply-templates select="body"/>
        </office:document-content>
    </xsl:template>

    <xsl:template match="body">
        <office:body>
            <office:text text:use-soft-page-breaks="true">
                <text:sequence-decls>
                    <text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
                    <text:sequence-decl text:display-outline-level="0" text:name="Table"/>
                    <text:sequence-decl text:display-outline-level="0" text:name="Text"/>
                    <text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
                    <text:sequence-decl text:display-outline-level="0" text:name="Figure"/>
                </text:sequence-decls>
                <xsl:apply-templates select="node()"/>
            </office:text>
        </office:body>
    </xsl:template>

    <xsl:template match="div[@class='salta_pag']">
        <text:p text:style-name="PsaltoPagina">
        </text:p>
    </xsl:template>

    <xsl:template match="h1">
        <text:h text:style-name="Heading_20_1" text:outline-level="1">
            <xsl:apply-templates select="node()"/>
        </text:h>
    </xsl:template>

    <xsl:template match="h2">
        <text:h text:style-name="Heading_20_2" text:outline-level="1">
            <xsl:apply-templates select="node()"/>
        </text:h>
    </xsl:template>

    <xsl:template match="h3">
        <text:h text:style-name="Heading_20_3" text:outline-level="1">
            <xsl:apply-templates select="node()"/>
        </text:h>
    </xsl:template>

    <xsl:template match="h4">
        <text:h text:style-name="Heading_20_4" text:outline-level="1">
            <xsl:apply-templates select="node()"/>
        </text:h>
    </xsl:template>

    <xsl:template match="a">
        <xsl:call-template name="text_applyer"/>
    </xsl:template>

    <xsl:template match="ul">
        <xsl:choose>
            <xsl:when test="@class='number'">
                <text:list text:style-name="L2">
                    <xsl:apply-templates select="node()"/>
                </text:list>
            </xsl:when>
            <xsl:when test="@class='bullet'">
                <text:list text:style-name="L1">
                    <xsl:apply-templates select="node()"/>
                </text:list>
            </xsl:when>
            <xsl:when test="@class='indent'">
                <xsl:for-each select="li">
                    <xsl:choose>
                        <xsl:when test="ul//li">
                            <xsl:apply-templates select="node()"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <text:p text:style-name="subApartado">
                                <xsl:apply-templates select="node()"/>
                            </text:p>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <!--	<xsl:apply-templates select="node()"/> -->
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="ol">
        <text:list text:style-name="L2">
            <xsl:apply-templates select="node()"/>
        </text:list>
    </xsl:template>

    <xsl:template match="li">
        <xsl:choose>
            <xsl:when test="ol">
                <text:list-item>
                    <text:p text:style-name="subApartado">
                        <xsl:apply-templates select="node()"/>
                    </text:p>
                    <xsl:apply-templates select="ol"/>
                </text:list-item>
            </xsl:when>
            <xsl:when test="ul">
                <text:list-item>
                    <text:p text:style-name="subApartado">
                        <xsl:apply-templates select="node()"/>
                    </text:p>
                    <xsl:apply-templates select="ul"/>
                </text:list-item>
            </xsl:when>
            <xsl:otherwise>
                <text:list-item>
                    <text:p text:style-name="subApartado">
                        <xsl:apply-templates select="node()"/>
                    </text:p>
                </text:list-item>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="text_applyer">
        <xsl:choose>
            <xsl:when test="h1|h2|h3|b">
                <text:h text:style-name="Heading_20_3" text:outline-level="3">
                    <xsl:value-of select="node()"/>
                </text:h>
            </xsl:when>
            <xsl:when test="text()">
                <xsl:value-of select="normalize-space(string(.))"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="node()"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="span">
        <xsl:choose>
            <xsl:when test="@class='link'">
                <xsl:call-template name="text_applyer"/>
            </xsl:when>
            <xsl:when test="@class='alert'">
                <text:span text:style-name="T2">
                    <xsl:value-of select="node()"/>
                </text:span>
            </xsl:when>
            <xsl:when test="@class='sortarrow'">
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="node()"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="p">
        <xsl:choose>
            <xsl:when test="@style='text-align:center'">
                <text:p text:style-name="P2">
                    <text:span text:style-name="T2">
                        <xsl:apply-templates select="node()"/>
                    </text:span>
                </text:p>
            </xsl:when>
            <xsl:when test="@style='text-align:right'">
                <text:p text:style-name="fecha">
                    <text:span text:style-name="T2">
                        <xsl:apply-templates select="node()"/>
                    </text:span>
                </text:p>
            </xsl:when>
            <xsl:when test="@class='sortarrow'">
            </xsl:when>
            <xsl:otherwise>
                <text:p text:style-name="parrafo">
                    <xsl:apply-templates select="node()"/>
                </text:p>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="cabecera">
        <text:p text:style-name="cabecera">
            <text:span text:style-name="T1">
                <xsl:apply-templates select="node()"/>
            </text:span>
        </text:p>
    </xsl:template>

    <xsl:template match="cabecera_end">
        <text:tab/>
        <xsl:apply-templates select="node()"/>
    </xsl:template>

    <xsl:template match="separacion">
        <text:p text:style-name="separacion">
        </text:p>
    </xsl:template>

    <xsl:template match="ref">
        <text:tab/>
        <xsl:apply-templates select="node()"/>
    </xsl:template>

    <xsl:template match="fecha">
        <text:p text:style-name="fecha">
            <text:span text:style-name="T2">
                <xsl:apply-templates select="node()"/>
            </text:span>
        </text:p>
    </xsl:template>

    <xsl:template match="strong">
        <text:span text:style-name="T4">
            <xsl:apply-templates select="node()"/>
        </text:span>
    </xsl:template>

    <xsl:template match="u">
        <text:span text:style-name="T5">
            <xsl:apply-templates select="node()"/>
        </text:span>
    </xsl:template>

    <xsl:template match="em">
        <text:span text:style-name="T6">
            <xsl:apply-templates select="node()"/>
        </text:span>
    </xsl:template>

    <xsl:template match="page">
        <text:p text:style-name="P5"/>
    </xsl:template>

    <xsl:template match="docs">
        <text:p text:style-name="Standard">
            <xsl:apply-templates select="node()"/>
        </text:p>
    </xsl:template>

</xsl:stylesheet>
