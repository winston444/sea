<?php
/**
 * This file contains the implementation for the TPE1 frame
 *
 * PHP version 5
 *
 * Copyright (C) 2006-2007 Alexander Merz
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category  File_Formats
 * @package   MP3_IDv2
 * @author    Alexander Merz <alexander.merz@web.de>
 * @copyright 2006-2007 Alexander Merz
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL 2.1
 * @version   CVS: $Id: TPE1.php 248624 2007-12-20 19:07:33Z alexmerz $
 * @link      http://pear.php.net/package/MP3_IDv2
 * @since     File available since Release 0.1
 */

/**
 * load parent class
 */
require_once 'MP3/IDv2/Frame/CommonText.php';

/**
 * Data stucture for TPE1 frame in a tag
 * (Lead artist(s)/Lead performer(s)/Soloist(s)/Performing group)
 *
 * @category File_Formats
 * @package  MP3_IDv2
 * @author   Alexander Merz <alexander.merz@web.de>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL 2.1
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/MP3_IDv2
 * @since    Class available since Release 0.1.0
 */
class MP3_IDv2_Frame_TPE1 extends MP3_IDv2_Frame_CommonText
{

    /**
     * Sets the id and purpose of the frame only
     *
     * @return void
     * @access public
     */
    public function __construct()
    {
        $this->setId("TPE1");
        $this->setPurpose("Lead artist(s)/Lead performer(s)/".
                          "Soloist(s)/Performing group");
    }

    /**
     * Returns the artists as list of strings
     *
     * @return array list of the artists
     * @access public
     */
    public function getArtists()
    {
        return explode('/', $this->getText());
    }

    /**
     * Adds an artist to the artists
     *
     * @param string $artist an artist
     *
     * @return void
     * @access public
     */
    public function addArtist($artist)
    {
        $t = $this->getText();
        if ("" == $t) {
            $t = $artist;
        } else {
            $t = $t."/".$artist;
        }
        $this->setText($t);
    }

}
?>