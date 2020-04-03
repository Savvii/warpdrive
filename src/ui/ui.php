<?php
namespace Savvii;

interface UIElement {
	public function render();
}

abstract class HtmlElement implements UIElement {
	protected $innerHtml;
	protected $attrib_array = array();
	protected $attributes = "";

	function add_attribute ( $attribute_name, $value ) {
		if (!empty( $this->attrib_array[ $attribute_name ] )) {	
			$this->attrib_array[ $attribute_name ] .= ' '.trim( $value );
		} else {
			if (array_key_exists( $attribute_name, $this->attrib_array )) {
				$this->attrib_array[ $attribute_name ] .= trim( $value );
			} else {
				$this->attrib_array[ $attribute_name ] = trim( $value );
			}
		}
		return $this;
	}

	function inner( $innerHtml ) {
		$this->innerHtml = $innerHtml;
		return $this;
	}

	function add_to_body( $content ) {
		if ( $content instanceof UIElement)
		{
			$this->innerHtml .= $content->render();
		}
		return $this;
	}

	/* must be called after all attributes have been added */
	function build_attributes() {
		foreach( $this->attrib_array as $attribute_name => $attribute_value )
		{
			$this->attributes .= "$attribute_name='$attribute_value'";
		}
	}
}

class Option extends HtmlElement {
	function render() {
		return <<<EOT
		<option $this->attributes>
		</option>
		EOT;
	}
}

class Postbox extends HtmlElement {
	protected $title;

	function __construct( $title )
	{
		$this->title = $title;
	}

	function render() {
		return <<<EOT
		<div class="postbox">
			<h2 class="hndle">$this->title</h2>
			<div class="inside">
			$this->innerHtml
			</div>
		</div>
		EOT;
	}
}

class PostboxContainer extends HtmlElement {
	function render() {
		return <<<EOT
		<div class="savvii dashboard-widgets-wrap">
			<div id="dashboard-widgets" class="metabox-holder">
				<div class="postbox-container">
				$this->innerHtml
				</div>
			</div>
		</div>
		EOT;       
	}
}
?>