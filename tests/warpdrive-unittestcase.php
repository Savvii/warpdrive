<?php

class Warpdrive_UnitTestCase extends WP_UnitTestCase {
    function _action_added( $tag ) {
        global $wp_filter;

        // Calulate the number of hooks originaly registered for $tag
        $saved_hook_count = 0;
        if ( isset( self::$hooks_saved['wp_filter'] ) ) {
            if ( isset( self::$hooks_saved['wp_filter'][ $tag ] ) ) {
                foreach ( self::$hooks_saved['wp_filter'][ $tag ] as $priority ) {
                    $saved_hook_count += count( $priority );
                }
            }
        }

        // Caclulate the number of hook currently registered for $tag
        $current_hook_count = 0;
        if ( isset( $wp_filter[ $tag ] ) ) {
            foreach ( $wp_filter[ $tag ] as $priority ) {
                $current_hook_count += count( $priority );
            }
        }

        // Current hook count should be larger if a hook is added
        return $current_hook_count > $saved_hook_count;
    }

    /**
     * Switch between user roles
     * E.g. administrator, editor, author, contributor, subscriber
     * @param string $role
     *
     * Copied from the WordPress tests-lib, so we'll ignore codestyle
     */
    protected function _setRole( $role ) {
        // @codingStandardsIgnoreStart
        $post = $_POST;
        $user_id = self::factory()->user->create( array( 'role' => $role ) );
        wp_set_current_user( $user_id );
        $_POST = array_merge( $_POST, $post );
        // @codingStandardsIgnoreEnd
    }

    /**
     * Sets a protected property on a given object via reflection
     *
     * @param $object - instance in which protected value is being modified
     * @param $property - property on instance being modified
     * @param $value - new value of the property being modified
     *
     * @return void
     */
    protected function setProtectedProperty( $object, $property, $value ) {
        $reflection = new ReflectionClass( $object );
        $reflection_property = $reflection->getProperty( $property );
        $reflection_property->setAccessible( true );
        $reflection_property->setValue( $object, $value );
    }

    /**
     * Get a protected property from a given object via reflection
     *
     * @param $object - instance to get the property value from
     * @param $property - property to get from instance
     *
     * @return mixed value
     */
    protected function getProtectedProperty( $object, $property ) {
        $reflection = new ReflectionClass( $object );
        $reflection_property = $reflection->getProperty( $property );
        $reflection_property->setAccessible( true );

        return $reflection_property->getValue( $object );
    }

    /**
     * Backward compatibility for WP < 4.4
     */
    function __isset( $name ) {
        return 'factory' === $name;
    }

    function __get( $name ) {
        if ( 'factory' === $name ) {
            return self::factory();
        }
    }

    protected static function factory() {
        static $factory = null;
        if ( ! $factory ) {
            $factory = new WP_UnitTest_Factory();
        }
        return $factory;
    }
}
