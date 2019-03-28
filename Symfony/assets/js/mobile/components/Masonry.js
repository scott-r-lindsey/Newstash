import React from 'react';
import MasonryLayout from 'react-masonry-layout';
import PropTypes from 'prop-types';
import MasonryItem from "./MasonryItem";

const gutter = 20;

class Masonry extends React.Component {

  constructor(props) {
    super(props);

    const server = typeof window == 'undefined';

    this.state = {
      perPage: 10,
      items: Array(20).fill(),
      itemWidth: this.findTargetWidth(server),
      server: server,
      initialItems: props.initialItems,
    }
  }

  findTargetWidth(server) {
    if (!server) {
      return (window.innerWidth / 2) - (( gutter * 3 )/2);
    }
    return 0;
  }

  updateDimensions() {

    if (!this.state.server) {

      this.setState( {
          itemWidth: (window.innerWidth / 2) - (( gutter * 3 )/2),
      });
    }
    else{
      // server side?
    }
  }

  componentDidMount() {
    this.updateDimensions();
    if (!this.state.server) {
      window.addEventListener("resize", this.updateDimensions.bind(this));
    }
  }

  componentWillUnmount() {
    if (!this.state.server) {
      window.removeEventListener("resize", this.updateDimensions.bind(this));
    }
  }

  render() {

    return (
      <div className="App">

        <MasonryLayout
          id="masonry-layout"
          sizes={ [
            { columns: 2, gutter: 20 },
            { mq: '768px', columns: 3, gutter: 20 },
            //{ mq: '1024px', columns: 6, gutter: 20 }
          ] }
          style={{
            marginLeft:'auto',
            marginRight:'auto'
          }}
        >

          {this.state.initialItems.map(item  => {
              let height= 100;

              return (
                <MasonryItem
                  item={item}
                  width={this.state.itemWidth}
                  key={item.sig} />
              )
          }, this)}

        </MasonryLayout>
      </div>
    );
  }
}

Masonry.propTypes = {
  initialItems: PropTypes.array.isRequired,
  fetchAdditionalItems: PropTypes.func.isRequired,
};

export default Masonry;
