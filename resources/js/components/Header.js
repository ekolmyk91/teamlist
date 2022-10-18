import React, {Component} from 'react'
import {Link} from 'react-router-dom'
import {withTranslation} from 'react-i18next'
import {getCurrentUser} from '../api/Api'
import data from '../data/data.json';
import RequestFormPopup from './RequestFormPopup';

const handleLogout = () => {
    axios.post('/logout')
        .then(() => location.href = '/')
};

var menuFlag = true;

class Header extends Component {

	constructor (props) {
		super(props)
		this.state = {
			currentUser: [],
            requestPopup: false
		}
	}

	componentDidMount () {
		getCurrentUser().then(response => {
			this.setState({
				currentUser: response,
			});
		})
	}

    mobileMenu () {
        var body = document.body;

        if  ( menuFlag == false ) {
            menuFlag = true;
            body.classList.remove("m-menu");
        } else {
            menuFlag = false;
            body.classList.add("m-menu");
        }
    }

    toggleFormPopup = () => {
        this.setState({requestPopup: !this.state.requestPopup});
        $(".overlay").toggleClass("overlay--show")
        $('body').toggleClass("m-ovrf")
    }

    render() {

        const renderAuthButton = () => {
            let currentUser = this.state.currentUser,
                isLoggedIn = false;

            if (currentUser.length != 0) {
                if (currentUser.roles[0].name == 'admin') {
                    isLoggedIn = true;
                }
            }
            if (isLoggedIn) {
              return  <li><a onClick={() => window.location.href="/admin"} >{t(data.menu.admin)}</a></li>;
            }
        }


        const { t } = this.props;
        return (
            <header>
                <div className="wrapper">
                    <div className="divHeader">
                        <a title="Logo" href="index.html" className="header-logo">
                            <svg>
                                <use xlinkHref='#logo' />
                            </svg>
                        </a>
                        <ul className="navMenu">
                            <li><a onClick={this.toggleFormPopup}>{t(data.menu.request)}</a></li>
                            <li>
                                <Link to='/'>{t(data.menu.home)}</Link>
                            </li>
                            <li>
                                <Link to='/team'>{t(data.menu.team)}</Link>
                            </li>
                            <li>
                                <Link to='/logout' onClick={handleLogout}>{t(data.menu.logout)}</Link>
                            </li>
                            {renderAuthButton()}
                        </ul>
                    </div>
                    <a className="hamburger js-navOpenMenu" onClick={this.mobileMenu}>
                        <span></span>
                    </a>
                </div>
                <RequestFormPopup request={this.state.requestPopup} closePopup={this.toggleFormPopup.bind(this)} user={this.state.currentUser}/>
            </header>
        );
    }
}
export default withTranslation()(Header)
