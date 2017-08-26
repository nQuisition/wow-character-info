import React, { PropTypes } from 'react'
import { Link } from 'react-router'

class Header extends React.Component
{
  render ()
  {
    const spanStyle = {
      display: "block"
    }
    const imgStyle = {
      display: "block",
      margin: "auto"
    }
    return(
      <div class="main-header">
        <Link to="/"><span style={spanStyle}><img style ={imgStyle} src="./media/images/logo23v2.png" alt="Illusions" /></span></Link>
      </div>
    );
  }
}

export default Header;
