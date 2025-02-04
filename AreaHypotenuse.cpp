#include<iostream>
#include <cmath>
using namespace std;
void area(int b,int h);
void hypot(int b, int h);
int main()
{
	//eb3/56347/21
	
	float b,h;
	cout<<"Enter the base of the triangle";
	cin>>b;
	cout<< "Enter the height of the triangle";
	cin>>h;
	area(b,h);
	hypot(b,h);
	return 0;
}
void area(int b,int h)
{
	float area;
	area=(0.5*b*h);
	cout<<"The area of the triangle is "<<area;
	
}
void hypot(int b,int h)
{
	float hypot;
	hypot=(sqrt(pow(b,2)+pow(h,2)));
	cout<< "The hypotenuse of the triangle is :"<<hypot;
}