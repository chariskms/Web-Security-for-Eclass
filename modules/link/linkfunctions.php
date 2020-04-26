<?php
/*===========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2010  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/


// The function used to add a link or a category
// type = add a link or add a category
function addlinkcategory($type)
{
	global $catlinkstatus;
	global $msgErr;
	global $tool_content;
	global $dbname;
	$ok=true;

	if($type == "link")
	{
		global $tbl_link;
		global $urllink;
		global $title;
		global $description;
		global $selectcategory;
		global $langGiveURL;
		global $langLinkAdded;

		// $urllink=trim($urllink);
		$title=trim($title);
		$description=trim($description);

		$urllink = htmlspecialchars($urllink, ENT_QUOTES);
		$title = htmlspecialchars($title, ENT_QUOTES);
		$description = htmlspecialchars($description, ENT_QUOTES);

		// if title is empty, an error occurs
		if(empty($urllink))
		{
			$msgErr=$langGiveURL;

			$ok=false;
		}
		// if the title is empty, we use the url as the title
		else
		{
			
			//echo '<script type="text/javascript">alert("'.$urllink.'");</script>';

			if(empty($title))
			{
				$title=$urllink;
			}

			// we check weither the $url starts with http://, if not we add this
			if(strstr($urllink, '://') == false) {
				$urllink = "http://" . $urllink;
			}
			//echo '<script type="text/javascript">alert("'.$urllink.'");</script>';


			$selectcategory	= escapeSimple($selectcategory);
			// looking for the largest ordre number for this category
			$result=db_query("SELECT MAX(ordre) FROM  `".$tbl_link."` WHERE category='$selectcategory'", $dbname);

			list($orderMax)=mysql_fetch_row($result);

			$ordre=$orderMax+1;

			$title	= escapeSimple($title);
			$description	= escapeSimple($description);
			$selectcategory	= escapeSimple($selectcategory);
			$ordre	= escapeSimple($ordre);

			$urllink = escapeSimple($urllink);
			//$urllink = "http://" . $urllink;

			$sql="INSERT INTO `".$tbl_link."` (url, titre, description, category,ordre) VALUES ('$urllink','$title','$description','$selectcategory','$ordre')";
			$catlinkstatus=$langLinkAdded;

			unset($urllink,$title,$description,$selectcategory);
		}
	}
	if($type == "category")
	{
		global $tbl_categories;
		global $categoryname;
		global $description;
		global $langCategoryAdded;
		global $langGiveCategoryName;

		$categoryname=trim($categoryname);
		$categoryname = htmlspecialchars($categoryname, ENT_QUOTES);
		$description = htmlspecialchars($description, ENT_QUOTES);

		if(empty($categoryname))
		{
			$msgErr=$langGiveCategoryName;
			$ok=false;
		}
		else
		{
			$tbl_categories	= escapeSimple($tbl_categories);
			// looking for the largest ordre number for this category
			$result = db_query("SELECT MAX(ordre) FROM  `".$tbl_categories."`" , $dbname);

			list($orderMax) = mysql_fetch_row($result);

			$ordre=$orderMax+1;

			$categoryname	= escapeSimple($categoryname);
			$description	= escapeSimple($description);
			$ordre	= escapeSimple($ordre);

			$sql="INSERT INTO `".$tbl_categories."` (categoryname, description, ordre) VALUES ('$categoryname','$description', '$ordre')";

			$catlinkstatus=$langCategoryAdded;

			unset($categoryname,$description);
		}
	}

	db_query($sql, $dbname);
	return $ok;
}


function deletelinkcategory($type)
{
	global $tbl_categories;
	global $tbl_link;
	global $catlinkstatus;
	global $tool_content;
	global $dbname;

	$tbl_categories	= escapeSimple($tbl_categories);

	if ($type=="link")
	{
		global $id;
		global $langLinkDeleted;
		$id	= escapeSimple($id);
		$sql="DELETE FROM `".$tbl_link."` WHERE id='".$id."'";
		$catlinkstatus=$langLinkDeleted;
		unset($id);
	}
	if ($type=="category")
	{
		global $id;
		global $langCategoryDeleted;
		$id	= escapeSimple($id);
		// first we delete the category itself and afterwards all the links of this category.
		$sql="DELETE FROM `".$tbl_categories."` WHERE id='".$id."'";
		db_query($sql, $dbname);
		$sql="DELETE FROM `".$tbl_link."` WHERE category='".$id."'";
		$catlinkstatus=$langCategoryDeleted;
		unset($id);
	}
	db_query($sql, $dbname);
}


function editlinkcategory($type)
{
	global $tbl_categories;
	global $tbl_link;
	global $catlinkstatus;
	global $id;
	global $submitLink;
	global $submitCategory;
	global $tool_content;
	global $dbname, $langLinkMod;

	if ($type=="link")
	{
		global $urllink;
		global $title;
		global $description;
		global $category;

		// this is used to populate the link-form with the info found in the database
		if (!$submitLink)
		{
			$id	= escapeSimple($id);
			$sql="SELECT * FROM `".$tbl_link."` WHERE id='".$id."'";
			$result=db_query($sql, $dbname);
			if ($myrow=mysql_fetch_array($result))
			{
				$urllink = $myrow["url"];
				$title = $myrow["titre"];
				$description = $myrow["description"];
				$category = $myrow["category"];
			}
		}

		$urllink = htmlspecialchars($urllink, ENT_QUOTES);
		$title = htmlspecialchars($title, ENT_QUOTES);
		$description = htmlspecialchars($description, ENT_QUOTES);
		$category = htmlspecialchars($category, ENT_QUOTES);

		// this is used to put the modified info of the link-form into the database
		if ($submitLink)
		{
			global $langLinkModded;
			global $selectcategory;


			$title	= escapeSimple($title);
			$description	= escapeSimple($description);
			$selectcategory	= escapeSimple($selectcategory);
			$id	= escapeSimple($id);
			$urllink	= escapeSimple($urllink);
			$urllink = "http://" . $urllink;

			$sql="UPDATE `".$tbl_link."` set url='$urllink', titre='$title', description='$description', category='$selectcategory' WHERE id='".$id."'";
			db_query($sql, $dbname);
			$catlinkstatus=$langLinkMod;

		}
	}
	if ($type=="category")
	{
		global $description;
		global $categoryname;

		$tbl_categories	= escapeSimple($tbl_categories);
		$id	= escapeSimple($id);

		// this is used to populate the category-form with the info found in the database
		if (!$submitCategory)
		{
			$sql="SELECT * FROM `".$tbl_categories."` WHERE id='".$id."'";
			$result=db_query($sql, $dbname);
			if ($myrow=mysql_fetch_array($result))
			{
				$categoryname= $myrow["categoryname"];
				$description = $myrow["description"];
			}
		}

		$categoryname = htmlspecialchars($categoryname, ENT_QUOTES);
		$description = htmlspecialchars($description, ENT_QUOTES);
		$id = htmlspecialchars($id, ENT_QUOTES);
		

		// this is used to put the modified info of the category-form into the database
		if ($submitCategory)
		{
			global $langCategoryModded;
			$tbl_categories	= escapeSimple($tbl_categories);
			$categoryname	= escapeSimple($categoryname);
			$description	= escapeSimple($description);
			$id	= escapeSimple($id);

			$sql="UPDATE `".$tbl_categories."` set categoryname='$categoryname', description='$description' WHERE id='".$id."'";
			db_query($sql, $dbname);
			$catlinkstatus=$langCategoryModded;
		}
	}
}


function makedefaultviewcode($locatie)
{
	global $aantalcategories;
	global $view;
	global $tool_content;

	for($j = 0; $j <= $aantalcategories-1; $j++)
	{
		$view[$j]=0;
	}
	$view[intval($locatie)]="1";
}


/**
 * Function getNumberOfLinks
 * @param unknown_type $catid
 * @return int number of links
 */
function getNumberOfLinks($catid){
	global $tbl_link, $dbname;
	$catid	= escapeSimple($catid);
	$sqlLinks = "SELECT * FROM `".$tbl_link."` WHERE category='".$catid."' ORDER BY ordre DESC";
	$result = db_query($sqlLinks, $dbname);
	$numberoflinks=mysql_num_rows($result);
	return $numberoflinks;
}


function showlinksofcategory($catid)
{
	global $tbl_link;
	global $is_adminOfCourse;
	global $urlview;
	global $up, $down;
	global $langLinkDelconfirm;
	global $langDelete, $langUp, $langDown, $langModify, $langLinks, $langCategoryDelconfirm, $urlServer;
	global $tool_content;
	global $dbname;

	$catid	= escapeSimple($catid);

	$sqlLinks = "SELECT * FROM `".$tbl_link."` WHERE category='".$catid."' ORDER BY ordre DESC";
	$result = db_query($sqlLinks, $dbname);
	$numberoflinks=mysql_num_rows($result);

	$i=1;
	while ($myrow = mysql_fetch_array($result))
        {
                $myrow[3] = parse_tex($myrow[3]);
                $tool_content .= "
                        <tr>
                        <td>&nbsp;</td>
                        <td width='1%'><img src='$urlServer/template/classic/img/arrow_grey.gif' alt=\"".$langLinks."\" /></td>
                        <td class=\"left\"><a href=\"link_goto.php?link_id=".$myrow[0]."&amp;link_url=".urlencode($myrow[1])."\" target=\"_blank\">".$myrow[2]."</a>";
                if (!empty($myrow[3])) {
                        $tool_content .= "<br /><small>".q($myrow[3])."</small>";
                }
                $tool_content .= "</td>";

                if ($is_adminOfCourse)
                {
                        $tool_content .=  "
                                <td width='45' align='right'>";
                        if (isset($category))
                                $tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?action=editlink&amp;category=$category&amp;id=$myrow[0]&amp;urlview=$urlview\">";
                        else
                                $tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?action=editlink&amp;id=$myrow[0]&amp;urlview=$urlview\">";

                        $tool_content .=  "<img src=\"../../template/classic/img/edit.gif\" title=\"".$langModify."\" /></a>&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?action=deletelink&amp;id=".$myrow[0]."&amp;urlview=".$urlview."\" onclick=\"javascript:if(!confirm('".$langLinkDelconfirm."')) return false;\"><img src=\"../../template/classic/img/delete.gif\" title=\"".$langDelete."\" /></a>
                                </td>
                                <td width='45' align='right'>";
                        // DISPLAY MOVE UP COMMAND only if it is not the top link
                        if ($i!=1)
                        {
                                $tool_content .= "<a href=\"$_SERVER[PHP_SELF]?urlview=".$urlview."&amp;up=".$myrow["id"]."\"><img src=\"../../template/classic/img/up.gif\" title=\"".$langUp."\" /></a>";
                        }

                        // DISPLAY MOVE DOWN COMMAND only if it is not the bottom link
                        if($i < $numberoflinks)
                        {
                                $tool_content .= "<a href=\"$_SERVER[PHP_SELF]?urlview=".$urlview."&amp;down=".$myrow["id"]."\"><img src=\"../../template/classic/img/down.gif\" title=\"".$langDown."\" /></a>";
                        }
                        $tool_content .= "
                                </td>";
                } else {
                        $tool_content .=  "
                                <td width='45' align='right'>&nbsp;</td>
                                <td width='45' align='right'>&nbsp;</td>";
                }

                $tool_content .= "
                        </tr>";
                $i++;
        }

}

function showcategoryadmintools($categoryid)
{
	global $urlview;
	global $aantalcategories;
	global $catcounter;
	global $langDelete, $langModify, $langUp, $langDown, $langCatDel;
	global $tool_content;

	$tool_content .=  "
      <td width='45' align='right'><a href=\"$_SERVER[PHP_SELF]?action=editcategory&amp;id=$categoryid&amp;urlview=$urlview\"><img src=\"../../template/classic/img/edit.gif\" title=\"".$langModify."\" /></a>&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?action=deletecategory&amp;id=".$categoryid."&amp;urlview=".$urlview."\" onclick=\"javascript:if(!confirm('".$langCatDel."')) return false;\">". "<img src=\"../../template/classic/img/delete.gif\" title=\"".$langDelete."\" /></a>
      </td>";


	// DISPLAY MOVE UP COMMAND only if it is not the top link
		$tool_content .=  "
      <td width='45' align='right'>";
	if ($catcounter!=1)
	{
		$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?catmove=true&amp;up=".$categoryid."&amp;urlview=$urlview\"><img src=\"../../template/classic/img/up.gif\" title=\"".$langUp."\" /></a>";
	}
	// DISPLAY MOVE DOWN COMMAND only if it is not the bottom link
	if($catcounter < $aantalcategories)
	{
		$tool_content .=  "<a href=\"$_SERVER[PHP_SELF]?catmove=true&amp;down=".$categoryid."&amp;urlview=$urlview\"><img src=\"../../template/classic/img/down.gif\" title=\"".$langDown."\" /></a>";
	}
		$tool_content .=  "
      </td>
    </tr>";
	$catcounter++;
}

function movecatlink($catlinkid)
{
	global $catmove;
	global $up;
	global $down;
	global $tbl_link;
	global $tbl_categories;
	global $tool_content;
	global $dbname;

	if ($down)
	{
		$thiscatlinkId = $down;
		$sortDirection = "DESC";
	}
	if ($up)
	{
		$thiscatlinkId = $up;
		$sortDirection = "ASC";
	}


	// We check if it is a category we are moving or a link. If it is a category, a querystring catmove = true is present in the url
	if ($catmove=="true")
	{
		$movetable=$tbl_categories;
		$catid=$catlinkid;
	}
	else
	{
		$movetable=$tbl_link;
		//getting the category of the link
		$thiscatlinkId	= escapeSimple($thiscatlinkId);
		$movetable	= escapeSimple($movetable);

		$sql="SELECT category from `".$movetable."` WHERE id='$thiscatlinkId'";
		$result=db_query($sql, $dbname);
		$catid=mysql_fetch_array($result);
	}


	// this code is copied and modified from announcements.php
	if ($sortDirection)
	{
		if (!in_array(trim(strtoupper($sortDirection)), array('ASC', 'DESC'))) die("Bad sort direction used."); //sanity check of sortDirection var
		if ($catmove=="true")
		{


			$sortDirection	= escapeSimple($sortDirection);

			$sqlcatlinks="SELECT id, ordre FROM `".$movetable."` ORDER BY `ordre` $sortDirection";
		}
		else
		{
			$sortDirection	= escapeSimple($sortDirection);
			$catid[0]	= escapeSimple($catid[0]);
			$sqlcatlinks="SELECT id, ordre FROM `".$movetable."` WHERE category='".$catid[0]."' ORDER BY `ordre` $sortDirection";
		}
		$linkresult = db_query($sqlcatlinks, $dbname);
		while ($sortrow=mysql_fetch_array($linkresult))
		{
			// found the next announcement id and order
			if (isset($thislinkFound) && $thislinkFound == true)
			{
				$nextlinkId=$sortrow["id"];
				$nextlinkOrdre=$sortrow["ordre"];

				$nextlinkOrdre	= escapeSimple($nextlinkOrdre);
				$thiscatlinkId	= escapeSimple($thiscatlinkId);
				$thislinkOrdre	= escapeSimple($thislinkOrdre);
				$nextlinkId	= escapeSimple($nextlinkId);

				db_query("UPDATE `".$movetable."`
			             SET ordre = '$nextlinkOrdre'
			             WHERE id =  '$thiscatlinkId'", $dbname);

				db_query("UPDATE `".$movetable."`
			             SET ordre = '$thislinkOrdre'
						 WHERE id =  '$nextlinkId'", $dbname);

				break;
			}

			if ($sortrow["id"]==$thiscatlinkId )
			{
				$thislinkOrdre=$sortrow["ordre"];
				$thislinkFound = true;
			}
		}
	}
}
?>
