import React from "react";
import * as Constants from '../../constants';
import Loading from "../Trim/Loading";

import { Helmet } from "react-helmet";
import { Link } from "react-router-dom";
import Masonry from "../Masonry/Masonry";

const queryString = require('query-string');


class Search extends React.Component {

  constructor(props, context) {
    super(props, context);

    this.searchedFor = queryString.parse(this.props.query).query;

    this.state = {
      initialWorks: [],
      hasmore: true,
      matches: '---',
    };
  }

  static styles = theme => ({
    youSearched: {
      position:'relative',
      minHeight:'60px',
      backgroundColor: Constants.FireBrick,
      fontFamily: Constants.BoringFont,
      margin: '0px 20px 20px 20px',
      padding: '18px',
      color: 'white',
      boxShadow: '1px 1px 14px rgba(50,50,50,.75)',
      borderRadius: '5px',
      paddingLeft: '60px',
    },
    icon: {
      position:'absolute',
      top:'15px',
      left: '15px',
      verticalAlign:'middle',
      marginRight: '10px',
    }
  });

  loading = false;
  page = 0;

  fetchMoreContent = (handler) => {

    if (this.state.hasmore && !this.loading) {

      this.loading = true;
      this.page++;

      fetch(this.api + this.props.query + '&page=' + this.page)
        .then(response => response.json())
        .then(data => {
          this.loading = false;
          this.setState({
            hasmore: data.result.hasmore,
            matches: data.result.matches,
          });
          handler(data.result.works);
        })
    }
  }

  componentDidMount() {

    this.fetchMoreContent(
      items => {
        this.setState({
          initialWorks: items
        });
      }
    );
  }

  render() {

    const { classes } = this.props;

    return (
      <div>
        <Helmet>
          <title>{ this.title + ' ' + this.searchedFor }</title>
        </Helmet>
        <br />
        {
          (this.state.initialWorks.length == 0 )
            ? <Loading />
            :
              <div key={this.props.location.key}>
                { this.renderDescription() }
                <Masonry
                  initialItems={this.state.initialWorks}
                  fetchAdditionalItems={this.fetchMoreContent}
              />
            </div>
        }
        { this.state.hasmore ||
          <div style={{
              textAlign: 'center',
              padding: '60px 0',
              opacity: '.5',
          }}>
            <em> Copyright &copy; { new Date().getFullYear() } Books to Love</em>
          </div>
        }
      </div>
    );
  }
}

export default Search;
