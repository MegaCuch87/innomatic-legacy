<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Webservices;

/*!
 @class WebServicesUser

 @abstract Web services interface users.
 */
class WebServicesUser
{
    public $mLog;
    public $mRootDb;
    public $mUserId;
    public $mProfileId = 0;
    public $mDomainId = 0;

    /*!
     @function WebServicesUser

     @abstract class constructor.

     @param rootDb DataAccess class - Innomatic database handler.
     @param userId integer - User id serial.
     */
    public function __construct($rootda, $userId = '')
    {
        $this->mLog = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

        if ( $rootda ) $this->mRootDb = $rootda;
        else $this->mLog->LogDie( 'innomatic.webservicesuser',
                                  'Invalid Innomatic database handler' );

        $this->mUserId = $userId;
    }

    /*!
     @function Add

     @abstract Adds a new web services user.

     @discussion Anonymous user can be added by simply leaving username
     and password arguments empty.

     @param username string - Username.
     @param password string - Password in clear text.
     @param profileId integer - Profile serial, may be empty.

     @result True if the user has been added.
     */
    public function Add($username, $password, $profileId = 0, $domainId = 0)
    {
        $result = false;

        if ( $this->mRootDb ) {
            if ( !$this->mUserId ) {
                $domainId = (int)$domainId;
                if ( !strlen( $domainId ) ) $domainId = 0;

                // :NOTE: Alex Pagnoni 010710
                // $username can be empty, since we can accept
                // anonymous users

                // :TODO: Alex Pagnoni 010710
                // It should check if the profile exists

                $query = $this->mRootDb->execute( 'SELECT username '.
                                                  'FROM webservices_users '.
                                                  'WHERE username='.$this->mRootDb->formatText( $username ) );

                if ( !$query->getNumberRows() ) {
                    $this->mUserId = $this->mRootDb->getNextSequenceValue( 'webservices_users_id_seq' );

                    $result = $this->mRootDb->execute( 'INSERT INTO webservices_users '.
                                                       'VALUES ('.
                                                       $this->mUserId.','.
                                                       $this->mRootDb->formatText( $username ).','.
                                                       $this->mRootDb->formatText( md5( $password ) ).','.
                                                       $profileId.','.
                                                       $domainId.')' );

                    if ( $result ) {
                        $this->mProfileId = $profileId;
                        $this->mDomainId = $domainId;

                        $this->mLog->logEvent( 'Innomatic',
                                              'Created new web services profile user', \Innomatic\Logging\Logger::NOTICE );
                    } else {
                        $this->mLog->logEvent( 'innomatic.webservicesuser.add',
                                              'Unable to insert web services user into webservices_users table', \Innomatic\Logging\Logger::ERROR );
                    }
                }
            } else $this->mLog->logEvent( 'innomatic.webservicesuser.add',
                                        'Already assigned user for this object', \Innomatic\Logging\Logger::ERROR );
        } else $this->mLog->logEvent( 'innomatic.webservicesuser.add',
                                    'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR );

        return $result;
    }

    /*!
     @function setByAccount

     @abstract Set the user by his account, checking for username and password.

     @discussion This function checks if an account with the given username and password
     exists, and if it exists the object user id is set with the corresponding one.

     @param username string - Username to check.
     @param password string - Password to check.

     @result True if the account has been found.
     */
    public function setByAccount($username, $password)
    {
        $result = false;

        if ( $this->mRootDb ) {
            if ( !$this->mUserId ) {
                // :NOTE: Alex Pagnoni 010710
                // $username can be empty, since we can accept
                // anonymous users

                $query = $this->mRootDb->execute( 'SELECT * '.
                                                  'FROM webservices_users '.
                                                  'WHERE username='.$this->mRootDb->formatText( $username ).' '.
                                                  'AND password='.$this->mRootDb->formatText( md5( $password ) ) );

                if ( $query->getNumberRows() ) {
                    $this->mUserId = $query->getFields( 'id' );
                    $this->mProfileId = $query->getFields( 'profileid' );
                    $this->mDomainId = $query->getFields( 'domainid' );

                    $result = $this->mUserId;
                }
            } else $this->mLog->logEvent( 'innomatic.webservicesuser.setbyaccount',
                                        'Already assigned user for this object', \Innomatic\Logging\Logger::ERROR );
        } else $this->mLog->logEvent( 'innomatic.webservicesuser.setbyaccount',
                                    'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR );

        return $result;
    }

    /*!
     @function ProfileId

     @abstract Returns the user profile id.

     @result User profile id.
     */
    public function ProfileId()
    {
        $result = false;

        if ( $this->mRootDb ) {
            if ( $this->mUserId ) {
                $query = $this->mRootDb->execute( 'SELECT profileid '.
                                                  'FROM webservices_users '.
                                                  'WHERE id='.(int)$this->mUserId );
                if ( $query->getNumberRows() ) {
                    $result = $query->getFields( 'profileid' );
                }
            } else $this->mLog->logEvent( 'innomatic.webservicesuser.profileid',
                                        'Object not assigned to an user', \Innomatic\Logging\Logger::ERROR );
        } else $this->mLog->logEvent( 'innomatic.webservicesuser.profileid',
                                    'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR );

        return $result;
    }

    /*!
     @function Remove

     @abstract Removes a web services user.

     @result True it the user has been deleted. Function returns true even if the given user doesn't exists.
     */
    public function Remove()
    {
        $result = false;

        if ( $this->mRootDb ) {
            if ( $this->mUserId ) {
                $result = $this->mRootDb->execute( 'DELETE FROM webservices_users '.
                                                   'WHERE id='.(int)$this->mUserId );

                if ( $result ) {
                    $this->mLog->logEvent( 'Innomatic',
                                          'Removed web services profile user', \Innomatic\Logging\Logger::NOTICE );
                } else {
                    $this->mLog->logEvent( 'innomatic.webservicesuser.remove',
                                          'Unable to remove web services user from webservices_users table', \Innomatic\Logging\Logger::ERROR );
                }
            } else $this->mLog->logEvent( 'innomatic.webservicesuser.remove',
                                        'Object not assigned to an user', \Innomatic\Logging\Logger::ERROR );
        } else $this->mLog->logEvent( 'innomatic.webservicesuser.remove',
                                    'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR );

        return $result;
    }

    /*!
     @function ChangePassword

     @abstract Changes web services user password.

     @param newPassword string - New password in clear text.

     @result True if the password has been changed.
     */
    public function ChangePassword($newPassword)
    {
        $result = false;

        if ( $this->mRootDb ) {
            if ( $this->mUserId ) {
                $result = $this->mRootDb->execute( 'UPDATE webservices_users '.
                                                   'SET password='.$this->mRootDb->formatText( md5( $newPassword ) ).
                                                   'WHERE id='.(int)$this->mUserId );

                if ( $result ) {
                    $this->mLog->logEvent( 'Innomatic',
                                          'Change web services profile user password', \Innomatic\Logging\Logger::NOTICE );
                } else {
                    $this->mLog->logEvent( 'innomatic.webservicesuser.changepassword',
                                          'Unable to update web services user password into webservices_users table', \Innomatic\Logging\Logger::ERROR );
                }
            } else $this->mLog->logEvent( 'innomatic.webservicesuser.changepassword',
                                        'Object not assigned to an user', \Innomatic\Logging\Logger::ERROR );
        } else $this->mLog->logEvent( 'innomatic.webservicesuser.changepassword',
                                    'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR );

        return $result;
    }

    /*!
     @function CheckPassword

     @abstract Checks if the web services user password matches a given password.

     @param password string - Password to check in clear text.

     @result True if the password is the same of the web services user.
     */
    public function CheckPassword($password)
    {
        $result = false;

        if ( $this->mRootDb ) {
            if ( $this->mUserId ) {
                $query = $this->mRootDb->execute( 'SELECT FROM webservices_users '.
                                                  'WHERE id='.(int)$this->mUserID.
                                                  ' AND password='.$this->mRootDb->formatText( md5( $password ) ).')' );

                if ( $query->getNumberRows() ) $result = true;
            } else $this->mLog->logEvent( 'innomatic.webservicesuser.checkpassword',
                                        'Object not assigned to an user', \Innomatic\Logging\Logger::ERROR );
        } else $this->mLog->logEvent( 'innomatic.webservicesuser.checkpassword',
                                    'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR );

        return $result;
    }

    /*!
     @function AssignProfile

     @abstract Assign a profile to the user.

     @param profileId integer - Profile serial.

     @result True if the profile has been assigned.
     */
    public function AssignProfile($profileId)
    {
        $result = false;

        if ( $this->mRootDb ) {
            if ( $this->mUserId ) {
                if ( strlen( $profileId ) ) {
                    if ( $query = $this->mRootDb->execute( 'UPDATE webservices_users '.
                                                           'SET profileid='.(int)$profileId.' '.
                                                           'WHERE id='.(int)$this->mUserId ) ) $result = true;

                    else  $this->mLog->logEvent( 'innomatic.webservicesuser.assignprofile',
                                                 'Unable to update profile id in webservices_users table', \Innomatic\Logging\Logger::ERROR );
                } else $this->mLog->logEvent( 'innomatic.webservicesuser.assignprofile',
                                            'Empty profile id', \Innomatic\Logging\Logger::ERROR );
            } else $this->mLog->logEvent( 'innomatic.webservicesuser.assignprofile',
                                        'Object not assigned to an user', \Innomatic\Logging\Logger::ERROR );
        } else $this->mLog->logEvent( 'innomatic.webservicesuser.assignprofile',
                                    'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR );

        return $result;
    }

    /*!
     @function assignDomain

     @abstract Assigns a domain to the user.

     @param domainId integer - Domain serial.

     @result True if the domain has been assigned.
     */
    public function assignDomain($domainId)
    {
        $result = false;

        if ( $this->mRootDb ) {
            if ( $this->mUserId ) {
                $domainId = (int)$domainId;
                if ( !strlen( $domainId ) ) $domainId = 0;

                if ( $query = $this->mRootDb->execute(
                    'UPDATE webservices_users '.
                    'SET domainid='.(int)$domainId.' '.
                    'WHERE id='.(int)$this->mUserId ) ) $result = true;
            }
        }

        return $result;
    }
}
