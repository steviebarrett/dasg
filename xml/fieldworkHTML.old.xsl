<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:template match="/">
        <h2>
            <xsl:value-of select="/fieldworkDocument/title"/>
        </h2>
        <div id="metadata">    
            <dl>
                <xsl:for-each select="/fieldworkDocument/informant">
                    <xsl:if test="./attribute::personId !=''">
                        <xsl:variable name="informantId"><xsl:value-of select="@personId"/></xsl:variable>
                        <dt>Informant Name</dt>
                        <dd>
                            <a id="{$informantId}" class="fieldworkPerson" href="/fieldwork/people/{$informantId}" target="_blank">
                                <xsl:value-of select="./nameEnglish"/>
                                <xsl:if test="./nameGaelic != ''">
                                    <span> (
                                        <xsl:value-of select="./nameGaelic"/>
                                        )</span>
                                </xsl:if>
                            </a>
                        </dd>
                    </xsl:if>
                    <xsl:if test="./origin != ''">
                        <dt>Informant Origin</dt>
                        <dd>
                            <xsl:value-of select="./origin"/>
                        </dd>
                    </xsl:if>
                </xsl:for-each>
                
                <!--dt>Informant(s)</dt>
                <xsl:for-each select="/fieldworkDocument/informant">
                    <dd>
                        <dl>
                            <dt>Name</dt>
                            <dd>
                                <xsl:value-of select="./nameEnglish"/>
                                <xsl:if test="./nameGaelic != ''">
                                    <span> (
                                        <xsl:value-of select="./nameGaelic"/>
                                    )</span>
                                </xsl:if>
                            </dd>
                            <xsl:if test="./age != ''">
                                <dt>Age</dt>
                                <dd>
                                    <xsl:value-of select="./age"/>
                                </dd>
                            </xsl:if>
                            <xsl:if test="./origin != ''">
                                <dt>Origin</dt>
                                <dd>
                                    <xsl:value-of select="./origin"/>
                                </dd>
                            </xsl:if>
                        </dl>
                    </dd>
                </xsl:for-each-->
                <xsl:if test="/fieldworkDocument/location != ''">
                    <dt>Location</dt>
                    <dd>
                        <xsl:value-of select="/fieldworkDocument/location"/>
                    </dd>
                </xsl:if>
                <xsl:if test="/fieldworkDocument/date != ''">
                    <dt>Date</dt>
                    <dd>
                        <xsl:value-of select="/fieldworkDocument/date"/>
                    </dd>
                </xsl:if>
                <xsl:if test="/fieldworkDocument/fieldworker/attribute::personId !=''">
                    <xsl:variable name="fieldworkerId"><xsl:value-of select="/fieldworkDocument/fieldworker/attribute::personId"/></xsl:variable>
                    <dt>Fieldworker</dt>
                    <dd>
                        <a id="{$fieldworkerId}" class="fieldworkPerson" href="/fieldwork/people/{$fieldworkerId}" target="_blank">
                            <xsl:value-of select="/fieldworkDocument/fieldworker"/>
                        </a>
                    </dd>
                </xsl:if> 
                <xsl:if test="/fieldworkDocument/note != ''">
                    <dt>Notes</dt>
                    <dd>
                        <ul>
                            <xsl:for-each select="/fieldworkDocument/note">
                                <li>
                                    <xsl:value-of select="."/>
                                </li>
                            </xsl:for-each>
                        </ul>
                    </dd>
                </xsl:if>
            </dl>       
        </div>
        <table class="fieldworkTable">
            <xsl:for-each select="/fieldworkDocument/items/child::*">
                <xsl:variable name="headword">
                    <xsl:value-of select="headword"/>
                </xsl:variable>
                <tr>
                    <xsl:choose>
                        <xsl:when test="headword/@type = 'heading'">
                            <td colspan="2">
                                <strong>
                                    <xsl:variable name="id">
                                        <xsl:value-of select="headword/@id"/>
                                    </xsl:variable>
                                    <a id="{$id}"/>
                                    <xsl:copy-of select="$headword"/>
                                </strong>
                            </td>
                        </xsl:when>
                        <xsl:otherwise>
                            <td>
                                <xsl:choose>
                                    <xsl:when test="name(.) = 'illustration'">
                                        <img src="/fieldwork/images/{@src}" width="{@width}"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:variable name="id">
                                            <xsl:value-of select="headword/@id"/>
                                        </xsl:variable>
                                        <a id="{$id}"/>
                                        <xsl:copy-of select="$headword"/>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </td>
                            <td>
                                <xsl:value-of select="description"/>
                                <xsl:if test="description/illustration/@src != ''">
                                    <xsl:for-each select="description/illustration">
                                        <div>
                                            <img src="/fieldwork/images/{@src}" width="{@width}"/>
                                        </div>
                                    </xsl:for-each>
                                </xsl:if>
                                <xsl:for-each select="note">
                                    <xsl:value-of select="."/>
                                </xsl:for-each>
                            </td>
                        </xsl:otherwise>
                    </xsl:choose>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>
</xsl:stylesheet>