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
            <style:font-face style:name="Calibri" svg:font-family="Calibri" style:font-family-generic="system" style:font-pitch="variable"/>
            <style:font-face style:name="DejaVu Serif" svg:font-family="&apos;DejaVu Serif&apos;" style:font-adornments="Book" style:font-family-generic="roman" style:font-pitch="variable"/>
            <style:font-face style:name="Droid Sans Fallback" svg:font-family="&apos;Droid Sans Fallback&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
            <style:font-face style:name="FreeSans" svg:font-family="FreeSans" style:font-family-generic="system" style:font-pitch="variable"/>
            <style:font-face style:name="Liberation Sans" svg:font-family="&apos;Liberation Sans&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
            <style:font-face style:name="Liberation Serif" svg:font-family="&apos;Liberation Serif&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
            <style:font-face style:name="Lohit Devanagari" svg:font-family="&apos;Lohit Devanagari&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
            <style:font-face style:name="OpenSymbol" svg:font-family="OpenSymbol" style:font-charset="x-symbol"/>
            <style:font-face style:name="OpenSymbol1" svg:font-family="OpenSymbol" style:font-family-generic="roman" style:font-pitch="variable"/>
            <style:font-face style:name="OpenSymbol2" svg:font-family="OpenSymbol" style:font-family-generic="system" style:font-pitch="variable"/>
            <style:font-face style:name="StarSymbol" svg:font-family="StarSymbol"/>
            <style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>
            <style:font-face style:name="Times New Roman1" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
            </office:font-face-decls>
            <office:automatic-styles>
            <style:style style:name="P1" style:family="paragraph" style:parent-style-name="parrafo">
            <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
            <style:text-properties officeooo:paragraph-rsid="002d4d2b"/>
            </style:style>
            <style:style style:name="P2" style:family="paragraph" style:parent-style-name="parrafo">
            <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
            <style:text-properties style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" officeooo:rsid="002d4d2b" officeooo:paragraph-rsid="002d4d2b"/>
            </style:style>
            <style:style style:name="P3" style:family="paragraph" style:parent-style-name="parrafo">
            <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
            <style:text-properties style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" officeooo:rsid="002e11ff" officeooo:paragraph-rsid="002e11ff"/>
            </style:style>
            <style:style style:name="P4" style:family="paragraph" style:parent-style-name="parrafo" style:list-style-name="Numbering_20_123">
            <style:text-properties officeooo:paragraph-rsid="002e11ff"/>
            </style:style>
            <style:style style:name="P5" style:family="paragraph" style:parent-style-name="parrafo" style:list-style-name="Numbering_20_123">
            <style:paragraph-properties fo:margin-left="0cm" fo:margin-right="0cm" fo:text-indent="0cm" style:auto-text-indent="false"/>
            <style:text-properties style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color" officeooo:rsid="002e11ff" officeooo:paragraph-rsid="0030ef33"/>
            </style:style>
            <style:style style:name="P6" style:family="paragraph" style:parent-style-name="parrafo" style:list-style-name="L1">
            <style:text-properties officeooo:paragraph-rsid="0030ef33"/>
            </style:style>
            <style:style style:name="P7" style:family="paragraph" style:parent-style-name="subApartado" style:list-style-name="Numbering_20_abc">
            <style:text-properties officeooo:paragraph-rsid="002e11ff"/>
            </style:style>
            <style:style style:name="P8" style:family="paragraph" style:parent-style-name="subApartado" style:list-style-name="L1">
            <style:text-properties officeooo:paragraph-rsid="0030ef33"/>
            </style:style>
            <style:style style:name="P9" style:family="paragraph" style:parent-style-name="subApartado2" style:list-style-name="Numbering_20_abc">
            <style:text-properties officeooo:paragraph-rsid="002e11ff"/>
            </style:style>
            <style:style style:name="P10" style:family="paragraph" style:parent-style-name="subApartado2" style:list-style-name="L1">
            <style:text-properties officeooo:paragraph-rsid="0030ef33"/>
            </style:style>
            <style:style style:name="T1" style:family="text">
            <style:text-properties fo:font-size="13pt" style:font-size-asian="13pt" style:font-size-complex="13pt"/>
            </style:style>
            <style:style style:name="T2" style:family="text">
            <style:text-properties officeooo:rsid="002d4d2b"/>
            </style:style>
            <style:style style:name="T3" style:family="text">
            <style:text-properties style:font-name="DejaVu Serif" fo:font-size="13pt" fo:language="es" fo:country="ES" officeooo:rsid="002d4d2b"/>
            </style:style>
            <style:style style:name="T4" style:family="text">
            <style:text-properties style:font-name="DejaVu Serif" fo:font-size="13pt" fo:language="es" fo:country="ES" officeooo:rsid="002e11ff"/>
            </style:style>
            <style:style style:name="T5" style:family="text">
            <style:text-properties officeooo:rsid="0030ef33"/>
            </style:style>
            <text:list-style style:name="L1">
            <text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" loext:num-list-format="%1%." style:num-suffix="." text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.134cm" fo:text-indent="-0.635cm" fo:margin-left="2.134cm"/>
            </style:list-level-properties>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" loext:num-list-format="%2%." style:num-suffix="." text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="2.769cm" fo:text-indent="-0.635cm" fo:margin-left="2.769cm"/>
            </style:list-level-properties>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" loext:num-list-format="%3%." style:num-suffix="." text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="3.404cm" fo:text-indent="-0.635cm" fo:margin-left="3.404cm"/>
            </style:list-level-properties>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" loext:num-list-format="%4%." style:num-suffix="." text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.039cm" fo:text-indent="-0.635cm" fo:margin-left="4.039cm"/>
            </style:list-level-properties>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" loext:num-list-format="%5%." style:num-suffix="." text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="4.674cm" fo:text-indent="-0.635cm" fo:margin-left="4.674cm"/>
            </style:list-level-properties>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" loext:num-list-format="%6%." style:num-suffix="." text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.309cm" fo:text-indent="-0.635cm" fo:margin-left="5.309cm"/>
            </style:list-level-properties>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" loext:num-list-format="%7%." style:num-suffix="." text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="5.944cm" fo:text-indent="-0.635cm" fo:margin-left="5.944cm"/>
            </style:list-level-properties>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" loext:num-list-format="%8%." style:num-suffix="." text:bullet-char="◦">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="6.579cm" fo:text-indent="-0.635cm" fo:margin-left="6.579cm"/>
            </style:list-level-properties>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" loext:num-list-format="%9%." style:num-suffix="." text:bullet-char="▪">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.214cm" fo:text-indent="-0.635cm" fo:margin-left="7.214cm"/>
            </style:list-level-properties>
            </text:list-level-style-bullet>
            <text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" loext:num-list-format="%10%." style:num-suffix="." text:bullet-char="•">
            <style:list-level-properties text:list-level-position-and-space-mode="label-alignment">
            <style:list-level-label-alignment text:label-followed-by="listtab" text:list-tab-stop-position="7.849cm" fo:text-indent="-0.635cm" fo:margin-left="7.849cm"/>
            </style:list-level-properties>
            </text:list-level-style-bullet>
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
        <text:h text:style-name="Heading_20_2" text:outline-level="2">
            <xsl:apply-templates select="node()"/>
        </text:h>
    </xsl:template>

    <xsl:template match="h3">
        <text:h text:style-name="Heading_20_3" text:outline-level="3">
            <xsl:apply-templates select="node()"/>
        </text:h>
    </xsl:template>

    <xsl:template match="h4">
        <text:h text:style-name="Heading_20_4" text:outline-level="4">
            <xsl:apply-templates select="node()"/>
        </text:h>
    </xsl:template>

    <xsl:template match="a">
        <xsl:call-template name="text_applyer"/>
    </xsl:template>

    <xsl:template match="ul">
        <xsl:choose>
            <xsl:when test="@class='number'">
                <text:list text:style-name="Numbering_20_abc">
                    <xsl:apply-templates select="node()"/>
                </text:list>
            </xsl:when>
            <xsl:when test="@class='bullet'">
                <text:list text:style-name="L1">
                    <xsl:apply-templates select="node()"/>
                </text:list>
            </xsl:when>
            <xsl:when test="@class='indent'">
                <xsl:apply-templates select="node()"/>
            </xsl:when>
            <xsl:otherwise>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="ul//ul">
        <xsl:choose>
            <xsl:when test="@class='number'">
                <text:list text:style-name="Numbering_20_abc">
                    <xsl:apply-templates select="node()"/>
                </text:list>
            </xsl:when>
            <xsl:when test="@class='bullet'">
                <text:list text:style-name="L1">
                    <xsl:apply-templates select="node()"/>
                </text:list>
            </xsl:when>
            <xsl:when test="@class='indent'">
                <xsl:apply-templates select="node()"/>
            </xsl:when>
            <xsl:otherwise>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="ol">
        <text:list text:style-name="Numbering_20_123">
            <xsl:apply-templates select="node()"/>
        </text:list>
    </xsl:template>

    <xsl:template match="ol//ol">
        <text:list text:style-name="Numbering_20_abc">
            <xsl:apply-templates select="node()"/>
        </text:list>
    </xsl:template>

    <xsl:template match="li">
        <xsl:choose>
            <xsl:when test="parent::ol">
                <text:list-item>
                    <xsl:call-template name="nums_applyer"/>
                    <xsl:apply-templates select="ol"/>
                </text:list-item>
            </xsl:when>
            <xsl:when test="ul">
                <xsl:choose>
                    <xsl:when test="@class='number'">
                        <text:list-item>
                            <text:list text:style-name="Numbering_20_123">
                                <xsl:apply-templates select="node()[not(self::ul)]"/>
                            </text:list>
                        </text:list-item>
                    </xsl:when>
                    <xsl:when test="parent::ul[@class='bullet']">
                        <text:list-item>
                            <xsl:call-template name="bullet_applyer"/>
                            <xsl:apply-templates select="ul"/>
                        </text:list-item>
                    </xsl:when>
                    <xsl:when test="parent::ul[@class='indent']">
                        <xsl:call-template name="tabs_applyer"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:apply-templates select="node()[not(self::ul)]"/>
                    </xsl:otherwise>
                </xsl:choose>
                <xsl:apply-templates select="ul"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:choose>
                    <xsl:when test="parent::ul[@class='indent']">
                        <xsl:call-template name="tabs_applyer"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <text:list-item>
                            <xsl:call-template name="bullet_applyer"/>
                        </text:list-item>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="nums_applyer">
        <xsl:variable name="nodes" select="ancestor::ol"/>
        <xsl:choose>
            <xsl:when test="count($nodes) = 1">
                <text:p text:style-name="P4">
                    <xsl:apply-templates select="node()[not(self::ol)]"/>
                </text:p>
            </xsl:when>
            <xsl:when test="count($nodes) = 2">
                <text:h text:style-name="P7" text:outline-level="3">
                    <xsl:apply-templates select="node()[not(self::ol)]"/>
                </text:h>
            </xsl:when>
            <xsl:when test="count($nodes) = 3">
                <text:h text:style-name="P9" text:outline-level="3">
                    <xsl:apply-templates select="node()[not(self::ol)]"/>
                </text:h>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="bullet_applyer">
        <xsl:variable name="nodes" select="ancestor::ul"/>
        <xsl:choose>
            <xsl:when test="count($nodes) = 1">
                <text:p text:style-name="P6">
                    <xsl:apply-templates select="node()[not(self::ul)]"/>
                </text:p>
            </xsl:when>
            <xsl:when test="count($nodes) = 2">
                <text:h text:style-name="P8" text:outline-level="3">
                    <xsl:apply-templates select="node()[not(self::ul)]"/>
                </text:h>
            </xsl:when>
            <xsl:when test="count($nodes) = 3">
                <text:h text:style-name="P10" text:outline-level="3">
                    <xsl:apply-templates select="node()[not(self::ul)]"/>
                </text:h>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="tabs_applyer">
        <!-- para la versión de poner tabuladores
        <text:p text:style-name="P3">
        <xsl:for-each select="ancestor::ul">
            <text:tab/>
        </xsl:for-each>
            <xsl:apply-templates select="node()[not(self::ul)]"/>
        </text:p>
        -->
        <xsl:variable name="nodes" select="ancestor::ul"/>
        <xsl:choose>
            <xsl:when test="count($nodes) = 1">
                <text:p text:style-name="Lista1Sin">
                    <xsl:apply-templates select="node()[not(self::ul)]"/>
                </text:p>
            </xsl:when>
            <xsl:when test="count($nodes) = 2">
                <text:p text:style-name="Lista2Sin">
                    <xsl:apply-templates select="node()[not(self::ul)]"/>
                </text:p>
            </xsl:when>
            <xsl:when test="count($nodes) = 3">
                <text:p text:style-name="Lista3Sin">
                    <xsl:apply-templates select="node()[not(self::ul)]"/>
                </text:p>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="text_applyer">
        <xsl:choose>
            <xsl:when test="h1|h2|h3|b">
                <text:h text:style-name="Heading_20_3" text:outline-level="3">
                    <xsl:value-of select="node()"/>
                </text:h>
            </xsl:when>
            <xsl:when test="span">
                <xsl:value-of select="span"/>
            </xsl:when>
            <xsl:when test="strong">
                <xsl:value-of select="strong"/>
            </xsl:when>
            <xsl:when test="u">
                <xsl:value-of select="u"/>
            </xsl:when>
            <xsl:when test="em">
                <xsl:value-of select="em"/>
            </xsl:when>
            <xsl:when test="text()">
                <xsl:value-of select="normalize-space(string(.))"/>
            </xsl:when>
            <xsl:otherwise>
                rrr<!-- <xsl:apply-templates select="node()"/> -->
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
        <xsl:variable name="nodes" select="ancestor::p"/>
        <xsl:choose>
            <xsl:when test="count($nodes) = 0">
                <text:p text:style-name="parrafo">
                    <text:span text:style-name="T4">
                        <xsl:apply-templates select="node()"/>
                    </text:span>
                </text:p>
            </xsl:when>
            <xsl:otherwise>
                <text:span text:style-name="T4">
                    <xsl:apply-templates select="node()"/>
                </text:span>
            </xsl:otherwise>
        </xsl:choose>
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